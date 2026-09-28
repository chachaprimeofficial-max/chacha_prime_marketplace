<?php
namespace App\Http\Controllers;
use App\Services\PaymentCenterService;
use App\Services\PaymentReconciliationService;
use App\Services\Payments\GatewayRegistry;
use App\Services\Payments\PayPalWebhookMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentWebhookController extends Controller
{
 public function handle(Request $request,string $provider,PaymentCenterService $payments,GatewayRegistry $gateways,PaymentReconciliationService $reconciliation,PayPalWebhookMapper $paypalMapper){
  $raw=$request->getContent();$payload=json_decode($raw,true);
  if(!is_array($payload))return response()->json(['success'=>false,'message'=>'Invalid webhook payload.'],400);
  $gateway=$gateways->gateway($provider);
  if(!$gateway->verifyWebhook($request->headers->all(),$raw))return response()->json(['success'=>false,'message'=>'Webhook verification failed.'],401);
  $eventId=(string)($request->header('X-Event-Id')??$payload['id']??$payload['event_id']??hash('sha256',$raw));
  $eventType=$payload['type']??$payload['event_type']??null;
  $mapped=$provider==='paypal'?$paypalMapper->normalize($payload):$payload;
  $eventType=$mapped['event_type']??$eventType;$webhookId=$payments->receiveWebhook($provider,$eventId,$eventType,$payload);
  if(DB::table('payment_webhooks')->where('id',$webhookId)->value('status')==='processed')return response()->json(['success'=>true,'duplicate'=>true]);
  $transactionId=(int)($payload['payment_transaction_id']??$payload['transaction_id']??0);
  $providerReference=$mapped['provider_reference']??$payload['provider_reference']??$payload['reference']??null;
  $transactionReference=$mapped['transaction_reference']??null;
  if(!$transactionId&&$transactionReference)$transactionId=(int)DB::table('payment_transactions')->where('provider',$provider)->where('transaction_reference',$transactionReference)->value('id');
  if(!$transactionId&&$providerReference)$transactionId=(int)DB::table('payment_transactions')->where('provider',$provider)->where('provider_reference',$providerReference)->value('id');
  $status=$mapped['status']??$payload['status']??null;
  if(!$transactionId||!in_array($status,['pending','authorized','paid','failed','cancelled','refunded','partially_refunded'],true)){
   DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'ignored','processed_at'=>now(),'updated_at'=>now()]);return response()->json(['success'=>true,'ignored'=>true]);
  }
  return DB::transaction(function()use($provider,$payload,$mapped,$status,$transactionId,$webhookId,$payments,$reconciliation){
   $tx=DB::table('payment_transactions')->where('id',$transactionId)->lockForUpdate()->first();
   if(!$tx||$tx->provider!==$provider){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment transaction mismatch.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment transaction mismatch.'],422);}
   if(isset($mapped['amount'])&&$mapped['amount']!==null){$reconciliation->reconcile($transactionId,$status,(float)$mapped['amount'],(string)($mapped['currency']??$tx->currency));if(abs((float)$mapped['amount']-(float)$tx->amount)>0.01||strtoupper((string)($mapped['currency']??$tx->currency))!==strtoupper((string)$tx->currency)){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Provider amount or currency mismatch.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Provider amount or currency mismatch.'],422);}}
   if($status==='paid'){$order=$tx->order_id?DB::table('orders')->where('id',$tx->order_id)->lockForUpdate()->first():null;if(!$order||$order->user_id!=$tx->user_id||abs((float)$order->total_amount-(float)$tx->amount)>0.01||strtoupper((string)$order->currency)!==strtoupper((string)$tx->currency)){DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'failed','error_message'=>'Payment amount or currency does not match the order.','updated_at'=>now()]);return response()->json(['success'=>false,'message'=>'Payment amount or currency does not match the order.'],422);}}
   $payments->updateStatus($transactionId,$status,$providerReference,$payload['failure_code']??null,$payload['failure_message']??null);DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'processed','processed_at'=>now(),'updated_at'=>now()]);return response()->json(['success'=>true]);
  });
 }
}
