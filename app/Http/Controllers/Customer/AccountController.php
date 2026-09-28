<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function dashboard(Request $request){$uid=$request->user()->id;return view('customer.dashboard',['orders'=>DB::table('orders')->where('user_id',$uid)->latest()->limit(8)->get(),'orderCount'=>DB::table('orders')->where('user_id',$uid)->count(),'wallet'=>app(\App\Services\WalletService::class)->balance($uid)]);}
    public function orders(Request $request){return view('customer.orders',['orders'=>DB::table('orders')->where('user_id',$request->user()->id)->latest()->paginate(15)]);}
    public function order(Request $request,int $order){$item=DB::table('orders')->where('id',$order)->where('user_id',$request->user()->id)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();return view('customer.order',['item'=>$item,'items'=>$items]);}
    public function cancel(Request $request,int $order){$item=DB::table('orders')->where('id',$order)->where('user_id',$request->user()->id)->firstOrFail();abort_unless(in_array($item->status,['pending','confirmed']),422,'This order can no longer be cancelled.');DB::table('orders')->where('id',$order)->update(['status'=>'cancelled','updated_at'=>now()]);return back()->with('success','Order cancellation requested.');}
}
