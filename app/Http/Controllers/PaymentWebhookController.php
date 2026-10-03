<?php
namespace App\Http\Controllers;

use App\Services\PaymentCenterService;
use App\Services\Payments\GatewayRegistry;
use App\Services\Payments\PayPalWebhookMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
 public function handle(Request $request,string $provider,PaymentCenterService $payments,GatewayRegistry $gateways,PayPalWebhookMapper $paypalMapper){
  $raw=$request->getContent();$payload=json_decode($raw,true);
  if(!is_array($payload))return response()->json(['success'=>false,'message'=>'Invalid webhook payload.'],400);
  $gateway=$gateways->gateway($provider);
  if(!$gateway->verifyWebhook($request->headers->all(),$raw))return response()->json(['success'=>false,'message'=>'Webhook verification failed.'],401);
  $eventId=(string)($request->header('X-Event-Id')??$payload['id']??$payload['event_id']??hash('sha256',$raw));
  $eventType=$payload['type']??$payload['event_type']??null;
  $mapped=$provider==='paypal'?$paypalMapper->normalize($payload):$payload;
  $eventType=$mapped['event_type']??$eventType;
  $webhookId=$payments->receiveWebhook($provider,$eventId,$eventType,$payload);
  if(DB::table('payment_webhooks')->where('id',$webhookId)->value('status')==='processed')return response()->json(['success'=>true,'duplicate'=>true]);

  $providerReference=$mapped['provider_reference']??$payload['provider_reference']??$payload['reference']??null;
  $paymentId=(int)($payload['payment_id']??0);
  if(!$paymentId&&$providerReference)$paymentId=(int)DB::table('payments')->where('provider',$provider)->where('provider_transaction_id',$providerReference)->latest('id')->value('id');

  $status=$mapped['status']??$payload['status']??null;
  if(!$paymentId||!in_array($status,['pending','authorized','paid','failed','cancelled','refunded','partially_refunded'],true)){
   DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'ignored','processed_at'=>now(),'updated_at'=>now()]);
   return response()->json(['success'=>true,'ignored'=>true]);
  }

  return DB::transaction(function()use($paymentId,$status,$providerReference,$webhookId,$payments,$mapped){
   $payment=DB::table('payments')->where('id',$paymentId)->lockForUpdate()->first();
   if(!$payment){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment not found.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment not found.'],422);}
   if($payment->provider!==request()->route('provider')){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment provider mismatch.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment provider mismatch.'],422);}
   if(isset($mapped['amount'])&&$mapped['amount']!==null&&abs((float)$mapped['amount']-(float)$payment->amount)>0.01){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment amount mismatch.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment amount mismatch.'],422);}
   if(isset($mapped['currency'])&&$mapped['currency']&&strtoupper($mapped['currency'])!==strtoupper($payment->currency)){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment currency mismatch.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment currency mismatch.'],422);}
   $payments->updateStatus($paymentId,$status,$providerReference,null,null);
   DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'processed','processed_at'=>now(),'updated_at'=>now()]);
   return response()->json(['success'=>true]);
  });
 }
}