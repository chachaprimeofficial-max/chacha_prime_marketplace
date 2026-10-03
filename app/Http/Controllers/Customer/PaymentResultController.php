<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentResultController extends Controller
{
 public function show(Request $request,int $payment){
  $tx=DB::table('payments as p')->leftJoin('orders as o','o.id','=','p.order_id')->where('p.id',$payment)->where('o.user_id',$request->user()->id)->first(['p.id','p.status','p.provider_transaction_id','p.provider','p.amount','p.currency','p.order_id','o.order_number']);
  abort_unless($tx,404);
  $data=['title'=>'Payment status','message'=>'Your payment status has been recorded.','reference'=>$tx->provider_transaction_id,'orderId'=>$tx->order_id];
  if($tx->status==='paid'){$data['title']='Payment successful';$data['message']='Your payment has been confirmed and your order is ready for the next fulfillment step.';}
  elseif($tx->status==='failed'){$data['title']='Payment failed';$data['message']='The payment provider reported a failure. No successful payment was recorded.';}
  elseif($tx->status==='cancelled'){$data['title']='Payment cancelled';$data['message']='This payment was cancelled. You can return to checkout and try another method.';}
  elseif($tx->status==='pending'){$data['title']='Payment pending';$data['message']='We are waiting for confirmation from the payment provider.';}
  elseif($tx->status==='authorized'){$data['title']='Payment authorized';$data['message']='Your payment has been authorized and the order is moving to fulfillment.';}
  elseif($tx->status==='partially_refunded'){$data['title']='Payment partially refunded';$data['message']='Part of this payment has been refunded.';}
  elseif($tx->status==='refunded'){$data['title']='Payment refunded';$data['message']='This payment has been fully refunded.';}
  return view('customer.payment-result',$data);
 }
}