<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\PaymentCenterService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class CheckoutPaymentController extends Controller
{
 public function methods(){return response()->json(['methods'=>DB::table('payment_methods')->where('enabled',1)->orderBy('sort_order')->get(['id','name','provider'])]);}
 public function store(Request $request,PaymentCenterService $payments,WalletService $wallet){$data=$request->validate(['order_id'=>'required|integer','provider'=>'required|string|max:100','amount'=>'required|numeric|min:0.01','currency'=>'nullable|string|max:10']);return DB::transaction(function()use($request,$data,$payments,$wallet){$order=DB::table('orders')->where('id',$data['order_id'])->where('user_id',$request->user()->id)->lockForUpdate()->firstOrFail();if(in_array($order->payment_status,['paid'],true))throw new RuntimeException('Order is already paid.');$amount=(float)$data['amount'];$currency=$data['currency']??'USD';$id=$payments->createForCheckout((int)$order->id,(int)$request->user()->id,$data['provider'],$amount,$currency);if($data['provider']==='wallet'){$wallet->debit((int)$request->user()->id,$amount,'purchase','Checkout payment for order #'.$order->id,'payment',$id);$payments->updateStatus($id,'paid');}elseif($data['provider']==='cod'){$payments->updateStatus($id,'authorized');} $tx=DB::table('payment_transactions')->where('id',$id)->first();return response()->json(['success'=>true,'payment_id'=>$id,'reference'=>$tx->transaction_reference,'status'=>$tx->status,'provider'=>$data['provider']]);});}
}
