<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\PaymentCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CheckoutPaymentController extends Controller
{
 public function methods(){return response()->json(['methods'=>DB::table('payment_methods')->where('enabled',1)->orderBy('sort_order')->get(['id','name','provider'])]);}
 public function store(Request $request,PaymentCenterService $payments){$data=$request->validate(['order_id'=>'required|integer','provider'=>'required|string|max:100','amount'=>'required|numeric|min:0.01','currency'=>'nullable|string|max:10']);$order=DB::table('orders')->where('id',$data['order_id'])->where('user_id',$request->user()->id)->firstOrFail();$id=$payments->createForCheckout((int)$order->id,(int)$request->user()->id,$data['provider'],(float)$data['amount'],$data['currency']??'USD');$tx=DB::table('payment_transactions')->where('id',$id)->first();return response()->json(['success'=>true,'payment_id'=>$id,'reference'=>$tx->transaction_reference,'status'=>$tx->status]);}
}
