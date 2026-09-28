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
 public function methods(){return response()->json(['methods'=>DB::table('payment_methods')->where('enabled',1)->orderBy('sort_order')->get(['id','name','provider'])]);}
 public function store(Request $request,PaymentCenterService $payments,WalletService $wallet,GatewayRegistry $gateways){
  $data=$request->validate(['order_id'=>'required|integer','provider'=>'required|string|max:100']);
  return DB::transaction(function()use($request,$data,$payments,$wallet,$gateways){
   $order=DB::table('orders')->where('id',$data['order_id'])->where('user_id',$request->user()->id)->lockForUpdate()->firstOrFail();
   if($order->payment_status==='paid')throw new RuntimeException('Order is already paid.');
   $amount=(float)$order->total_amount;
   $currency=(string)($order->currency?:config('chacha.brand.default_currency','USD'));
   if($amount<=0)throw new RuntimeException('This order does not require an online payment.');
   $provider=$data['provider'];
   $id=$payments->createForCheckout((int)$order->id,(int)$request->user()->id,$provider,$amount,$currency);
   if($provider==='wallet'){$wallet->debit((int)$request->user()->id,$amount,'purchase','Checkout payment for order #'.$order->id,'payment',$id);$payments->updateStatus($id,'paid');}
   elseif($provider==='cod'){$payments->updateStatus($id,'authorized');}
   $tx=DB::table('payment_transactions')->where('id',$id)->first();
   $gateway=$gateways->gateway($provider);
   $result=$gateway->createPayment((array)$tx);
   return response()->json(['success'=>true,'payment_id'=>$id,'reference'=>$tx->transaction_reference,'status'=>DB::table('payment_transactions')->where('id',$id)->value('status'),'provider'=>$provider,'amount'=>$amount,'currency'=>$currency,'redirect_url'=>$result['redirect_url']??null]);
  });
 }
}
