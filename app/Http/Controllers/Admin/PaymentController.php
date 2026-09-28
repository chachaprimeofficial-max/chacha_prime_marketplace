<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentController extends Controller
{
 public function index(Request $r){$q=DB::table('payments')->leftJoin('orders','orders.id','=','payments.order_id')->leftJoin('users','users.id','=','payments.user_id')->select('payments.*','orders.order_number','users.name as customer_name','users.email as customer_email')->latest('payments.id');if($r->filled('status'))$q->where('payments.status',$r->status);if($r->filled('provider'))$q->where('payments.provider',$r->provider);if($r->filled('search'))$q->where(function($x)use($r){$x->where('payments.transaction_reference','like','%'.$r->search.'%')->orWhere('orders.order_number','like','%'.$r->search.'%')->orWhere('users.email','like','%'.$r->search.'%');});$payments=$q->paginate(25)->withQueryString();$stats=['pending'=>DB::table('payments')->where('status','pending')->count(),'paid'=>DB::table('payments')->where('status','paid')->count(),'failed'=>DB::table('payments')->where('status','failed')->count(),'refunded'=>DB::table('payments')->where('status','refunded')->count()];return view('admin.payments.index',compact('payments','stats'));}
 public function show(int $payment){$payment=DB::table('payments')->leftJoin('orders','orders.id','=','payments.order_id')->leftJoin('users','users.id','=','payments.user_id')->select('payments.*','orders.order_number','orders.status as order_status','users.name as customer_name','users.email as customer_email')->where('payments.id',$payment)->firstOrFail();$refunds=DB::table('payment_refunds')->where('payment_id',$payment->id)->latest()->get();return view('admin.payments.show',compact('payment','refunds'));}
 public function verify(Request $r,int $payment){$d=$r->validate(['transaction_reference'=>'nullable|string|max:190']);app(PaymentService::class)->markPaid($payment,$d['transaction_reference']??null);return back()->with('success','Payment verified and order payment status updated.');}
 public function fail(Request $r,int $payment){$d=$r->validate(['reason'=>'required|string|max:500']);app(PaymentService::class)->markFailed($payment,$d['reason']);return back()->with('success','Payment marked as failed.');}
 public function refund(Request $r,int $payment){$d=$r->validate(['amount'=>'required|numeric|min:0.01','reason'=>'nullable|string|max:500']);app(PaymentService::class)->refund($payment,(float)$d['amount'],$d['reason']??null);return back()->with('success','Refund recorded.');}
}
