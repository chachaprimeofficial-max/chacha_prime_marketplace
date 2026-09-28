<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CouponController extends Controller
{
 public function index(){return view('admin.coupons.index',['coupons'=>DB::table('coupons')->latest('id')->paginate(30)]);}
 public function store(Request $request){$d=$request->validate(['code'=>'required|string|max:80','type'=>'required|in:fixed,percent','value'=>'required|numeric|min:0.01','min_order_amount'=>'nullable|numeric|min:0','max_uses'=>'nullable|integer|min:1','starts_at'=>'nullable|date','expires_at'=>'nullable|date|after_or_equal:starts_at','status'=>'nullable|in:active,inactive']);DB::table('coupons')->insert(['code'=>strtoupper(trim($d['code'])),'type'=>$d['type'],'value'=>$d['value'],'min_order_amount'=>$d['min_order_amount']??0,'max_uses'=>$d['max_uses']??null,'used_count'=>0,'starts_at'=>$d['starts_at']??null,'expires_at'=>$d['expires_at']??null,'status'=>$d['status']??'active','created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Coupon created.');}
 public function toggle(int $coupon){$c=DB::table('coupons')->where('id',$coupon)->firstOrFail();DB::table('coupons')->where('id',$coupon)->update(['status'=>$c->status==='active'?'inactive':'active','updated_at'=>now()]);return back()->with('success','Coupon status updated.');}
}
