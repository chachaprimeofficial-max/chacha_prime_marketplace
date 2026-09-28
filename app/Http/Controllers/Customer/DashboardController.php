<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
 public function index(){
  $uid=auth()->id();
  $stats=['orders'=>DB::table('orders')->where('user_id',$uid)->count(),'pending'=>DB::table('orders')->where('user_id',$uid)->whereIn('status',['pending','confirmed','processing'])->count(),'delivered'=>DB::table('orders')->where('user_id',$uid)->whereIn('status',['delivered','completed'])->count(),'returns'=>DB::table('returns')->where('user_id',$uid)->count()];
  $orders=DB::table('orders')->where('user_id',$uid)->latest('id')->limit(8)->get();
  $wallet=(float)(DB::table('wallets')->where('user_id',$uid)->value('balance')??0);
  $shipments=DB::table('shipments')->join('orders','orders.id','=','shipments.order_id')->where('orders.user_id',$uid)->whereNotIn('shipments.status',['delivered','returned'])->select('shipments.*','orders.order_number')->latest('shipments.id')->limit(5)->get();
  $notifications=DB::table('notifications')->where('user_id',$uid)->latest()->limit(6)->get();
  return view('customer.dashboard',compact('stats','orders','wallet','shipments','notifications'));
 }
}
