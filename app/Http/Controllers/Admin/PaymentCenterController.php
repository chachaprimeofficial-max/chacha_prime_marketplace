<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentCenterController extends Controller
{
 public function index(Request $request){$q=DB::table('payment_transactions as p')->leftJoin('orders as o','o.id','=','p.order_id')->leftJoin('users as u','u.id','=','p.user_id')->select('p.*','o.order_number','u.name as customer_name')->when($request->status,fn($x,$v)=>$x->where('p.status',$v))->when($request->provider,fn($x,$v)=>$x->where('p.provider',$v))->latest('p.id')->paginate(25)->withQueryString();return view('admin.payments.index',['payments'=>$q]);}
 public function show(int $payment){$p=DB::table('payment_transactions as p')->leftJoin('orders as o','o.id','=','p.order_id')->leftJoin('users as u','u.id','=','p.user_id')->select('p.*','o.order_number','u.name as customer_name','u.email')->where('p.id',$payment)->firstOrFail();$webhooks=DB::table('payment_webhooks')->where('provider',$p->provider)->latest()->limit(20)->get();return view('admin.payments.show',compact('p','webhooks'));}
 public function markStatus(Request $request,int $payment){$data=$request->validate(['status'=>'required|in:pending,authorized,paid,failed,cancelled,refunded,partially_refunded']);DB::table('payment_transactions')->where('id',$payment)->update(['status'=>$data['status'],'updated_at'=>now(),'paid_at'=>$data['status']==='paid'?now():null]);return back()->with('success','Payment status updated.');}
}
