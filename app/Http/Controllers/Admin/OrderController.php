<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
 public function index(Request $request){$q=DB::table('orders')->orderByDesc('id');if($request->filled('status'))$q->where('status',$request->status);if($request->filled('search'))$q->where('order_number','like','%'.$request->search.'%');return view('admin.orders.index',['orders'=>$q->paginate(25)->withQueryString()]);}
 public function show(int $order){$item=DB::table('orders')->where('id',$order)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();$payments=DB::table('payments')->where('order_id',$order)->latest()->get();return view('admin.orders.show',compact('item','items','payments'));}
 public function update(Request $request,int $order){$data=$request->validate(['status'=>'required|in:pending,confirmed,processing,packing,shipped,in_transit,out_for_delivery,delivered,cancelled,returned,refunded','tracking_number'=>'nullable|string|max:120','carrier'=>'nullable|string|max:120']);DB::transaction(function()use($request,$order,$data){$old=DB::table('orders')->where('id',$order)->lockForUpdate()->firstOrFail();if($old->status!==$data['status']&&$data['status']==='cancelled'&&!in_array($old->status,['cancelled','delivered','returned','refunded'])){$items=DB::table('order_items')->where('order_id',$order)->get();$inventory=app(InventoryService::class);foreach($items as $item)$inventory->restore((int)$item->product_id,$item->variant_id?((int)$item->variant_id):null,(int)$item->quantity,'cancel_restore','order',$order,$request->user()->id);DB::table('orders')->where('id',$order)->update(['status'=>'cancelled','updated_at'=>now()]);}else DB::table('orders')->where('id',$order)->update(array_merge($data,['updated_at'=>now()]));if($old->status!==$data['status'])app(NotificationService::class)->orderStatus((int)$old->user_id,$old->order_number,$data['status'],route('customer.orders.show',$order));if(!empty($data['tracking_number'])&&$data['tracking_number']!==($old->tracking_number??null))app(NotificationService::class)->send((int)$old->user_id,'Tracking updated','Your order '.$old->order_number.' has a new tracking number: '.$data['tracking_number'].'.','shipping',route('customer.orders.show',$order));});return back()->with('success','Order updated and inventory synchronized.');}
}
