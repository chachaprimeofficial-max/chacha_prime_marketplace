<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\PaymentCenterService;
use App\Services\WalletService;
use App\Services\Payments\GatewayRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutPaymentController extends Controller
{
 public function methods(){
  return response()->json(['methods'=>DB::table('payment_methods')->where('enabled',1)->orderBy('sort_order')->get(['id','name','provider'])]);
 }

 public function store(Request $request,PaymentCenterService $payments,WalletService $wallet,GatewayRegistry $gateways){
  $data=$request->validate(['order_id'=>'required|integer','provider'=>'required|string|max:100']);
  return DB::transaction(function()use($request,$data,$payments,$wallet,$gateways){
   $order=DB::table('orders')->where('id',$data['order_id'])->where('user_id',$request->user()->id)->lockForUpdate()->firstOrFail();
   if(in_array($order->status,['cancelled','refunded'],true)) throw new RuntimeException('This order cannot be paid.');
   $existing=DB::table('payments')->where('order_id',$order->id)->whereIn('status',['pending','authorized'])->latest('id')->first();
   if($existing && $existing->provider!==$data['provider']) throw new RuntimeException('A payment attempt is already in progress for this order.');
   $amount=(float)$order->total_amount;$currency=(string)($order->currency?:config('chacha.brand.default_currency','USD'));
   if($amount<=0)throw new RuntimeException('This order does not require an online payment.');
   $provider=$data['provider'];
   $paymentId=$existing?(int)$existing->id:$payments->createForCheckout((int)$order->id,(int)$request->user()->id,$provider,$amount,$currency);
   $payment=DB::table('payments')->where('id',$paymentId)->lockForUpdate()->first();
   if($provider==='wallet'){
    if($payment->status!=='paid'){$wallet->debit((int)$request->user()->id,$amount,'purchase','Checkout payment for order #'.$order->id,'payment',$paymentId);$payments->updateStatus($paymentId,'paid');}
    return response()->json(['success'=>true,'payment_id'=>$paymentId,'status'=>'paid','provider'=>$provider,'amount'=>$amount,'currency'=>$currency,'redirect_url'=>route('checkout.success',$order->id)]);
   }
   if($provider==='cod'){
    $payments->updateStatus($paymentId,'authorized');
    return response()->json(['success'=>true,'payment_id'=>$paymentId,'status'=>'authorized','provider'=>$provider,'amount'=>$amount,'currency'=>$currency,'redirect_url'=>route('checkout.success',$order->id)]);
   }
   $gateway=$gateways->gateway($provider);
   $result=$gateway->createPayment((array)$payment);
   if(!empty($result['provider_order_id'])||!empty($result['reference'])){
    DB::table('payments')->where('id',$paymentId)->update(['provider_transaction_id'=>$result['provider_order_id']??$result['reference'],'updated_at'=>now()]);
   }
   return response()->json(['success'=>true,'payment_id'=>$paymentId,'status'=>'pending','provider'=>$provider,'amount'=>$amount,'currency'=>$currency,'redirect_url'=>$result['redirect_url']??null,'reference'=>$result['reference']??null,'payload'=>$result['payload']??null]);
  });
 }
}