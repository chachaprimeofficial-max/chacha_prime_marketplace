<?php
namespace App\Http\Controllers;
use App\Services\PaymentCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentWebhookController extends Controller
{
 public function handle(Request $request,string $provider,PaymentCenterService $payments){$raw=$request->getContent();$payload=json_decode($raw,true);if(!is_array($payload))return response()->json(['success'=>false,'message'=>'Invalid webhook payload.'],400);$eventId=(string)($request->header('X-Event-Id')??$payload['id']??$payload['event_id']??hash('sha256',$raw));$eventType=$payload['type']??$payload['event_type']??null;$webhookId=$payments->receiveWebhook($provider,$eventId,$eventType,$payload);$transactionId=(int)($payload['payment_transaction_id']??$payload['transaction_id']??0);$status=$payload['status']??null;if($transactionId&&in_array($status,['pending','authorized','paid','failed','cancelled','refunded','partially_refunded'],true)){$payments->updateStatus($transactionId,$status,$payload['provider_reference']??$payload['reference']??null,$payload['failure_code']??null,$payload['failure_message']??null);DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'processed','processed_at'=>now(),'updated_at'=>now()]);}else{DB::table('payment_webhooks')->where('id',$webhookId)->update(['status'=>'ignored','processed_at'=>now(),'updated_at'=>now()]);}return response()->json(['success'=>true]);}
}
