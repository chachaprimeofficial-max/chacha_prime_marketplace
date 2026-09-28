<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AccountController extends Controller
{
 public function dashboard(Request $r){$uid=$r->user()->id;return view('customer.dashboard',['orders'=>DB::table('orders')->where('user_id',$uid)->latest()->limit(8)->get(),'orderCount'=>DB::table('orders')->where('user_id',$uid)->count(),'wallet'=>app(\App\Services\WalletService::class)->balance($uid)]);}
 public function orders(Request $r){return view('customer.orders',['orders'=>DB::table('orders')->where('user_id',$r->user()->id)->latest()->paginate(15)]);}
 public function order(Request $r,int $order){$item=DB::table('orders')->where('id',$order)->where('user_id',$r->user()->id)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();$shipment=DB::table('shipments')->where('order_id',$order)->latest('id')->first();$payments=DB::table('payments')->where('order_id',$order)->latest('id')->get();return view('customer.order',['item'=>$item,'items'=>$items,'shipment'=>$shipment,'payments'=>$payments]);}
 public function cancel(Request $r,int $order){$o=DB::table('orders')->where('id',$order)->where('user_id',$r->user()->id)->lockForUpdate()->firstOrFail();abort_unless(in_array($o->status,['pending','confirmed']),422,'This order can no longer be cancelled.');DB::transaction(function()use($o,$order,$r){foreach(DB::table('order_items')->where('order_id',$order)->get() as $i)app(\App\Services\InventoryService::class)->restore((int)$i->product_id,$i->variation_id?(int)$i->variation_id:null,(int)$i->quantity,'cancel_restore','order',$order,$r->user()->id);DB::table('orders')->where('id',$order)->update(['status'=>'cancelled','updated_at'=>now()]);});return back()->with('success','Order cancelled and inventory restored.');}
 public function notifications(Request $r){return view('customer.notifications',['notifications'=>DB::table('notifications')->where('user_id',$r->user()->id)->latest()->paginate(20)]);}
 public function markNotificationsRead(Request $r){DB::table('notifications')->where('user_id',$r->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);return back();}
 public function addresses(Request $r){return view('customer.addresses',['addresses'=>DB::table('user_addresses')->where('user_id',$r->user()->id)->latest()->get()]);}
 public function addressStore(Request $r){$d=$r->validate(['label'=>'required|string|max:80','recipient_name'=>'required|string|max:120','phone'=>'required|string|max:40','address'=>'required|string|max:1000','city'=>'required|string|max:120','state'=>'nullable|string|max:120','postal_code'=>'nullable|string|max:30','country_code'=>'required|string|size:2','is_default'=>'nullable|boolean']);DB::transaction(function()use($d,$r){$uid=$r->user()->id;if(!empty($d['is_default']))DB::table('user_addresses')->where('user_id',$uid)->update(['is_default'=>0]);DB::table('user_addresses')->insert(array_merge($d,['user_id'=>$uid,'is_default'=>(int)($d['is_default']??0),'created_at'=>now(),'updated_at'=>now()]));});return back()->with('success','Address saved.');}
 public function addressDelete(Request $r,int $address){DB::table('user_addresses')->where('id',$address)->where('user_id',$r->user()->id)->delete();return back()->with('success','Address removed.');}
 public function security(){return view('customer.security');}
}
