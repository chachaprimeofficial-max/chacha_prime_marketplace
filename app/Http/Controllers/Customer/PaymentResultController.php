<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentResultController extends Controller
{
 public function show(Request $request,int $payment){$tx=DB::table('payment_transactions as p')->leftJoin('orders as o','o.id','=','p.order_id')->where('p.id',$payment)->where('p.user_id',$request->user()->id)->first(['p.id','p.status','p.transaction_reference','p.provider','p.amount','p.currency','p.order_id','o.order_number']);abort_unless($tx,404);$data=['title'=>'Payment status','message'=>'Your payment status has been recorded.','reference'=>$tx->transaction_reference,'orderId'=>$tx->order_id];if($tx->status==='paid'){$data['title']='Payment successful';$data['message']='Your payment has been confirmed and your order is ready for the next fulfillment step.';}elseif($tx->status==='failed'){$data['title']='Payment failed';$data['message']='The payment provider reported a failure. No successful payment was recorded.';}elseif($tx->status==='cancelled'){$data['title']='Payment cancelled';$data['message']='This payment was cancelled. You can return to checkout and try another method.';}elseif($tx->status==='pending'){$data['title']='Payment pending';$data['message']='We are waiting for confirmation from the payment provider.';}return view('customer.payment-result',$data);}
}
