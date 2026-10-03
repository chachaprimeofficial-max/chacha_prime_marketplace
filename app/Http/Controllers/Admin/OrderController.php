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
 public function show(int $order){$item=DB::table('orders')->where('id',$order)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();$payments=DB::table('payments')->where('order_id',$order)->latest()->get();$shipment=DB::table('shipments')->where('order_id',$order)->latest('id')->first();return view('admin.orders.show',compact('item','items','payments','shipment'));}
 public function update(Request $request,int $order){
  $data=$request->validate(['status'=>'required|in:pending,confirmed,processing,packing,shipped,in_transit,out_for_delivery,delivered,cancelled,returned,refunded','tracking_number'=>'nullable|string|max:190','carrier'=>'nullable|string|max:120','service'=>'nullable|string|max:120']);
  DB::transaction(function()use($request,$order,$data){
   $old=DB::table('orders')->where('id',$order)->lockForUpdate()->firstOrFail();
   if($old->status!==$data['status']&&$data['status']==='cancelled'&&!in_array($old->status,['cancelled','delivered','returned','refunded'])){
    foreach(DB::table('order_items')->where('order_id',$order)->get() as $item) app(InventoryService::class)->restore((int)$item->product_id,$item->variant_id?(int)$item->variant_id:null,(int)$item->quantity,'cancel_restore','order',$order,$request->user()->id);
   }
   DB::table('orders')->where('id',$order)->update(['status'=>$data['status'],'updated_at'=>now()]);
   $shipment=DB::table('shipments')->where('order_id',$order)->latest('id')->first();
   if(array_key_exists('tracking_number',$data)||array_key_exists('carrier',$data)||array_key_exists('service',$data)){
    $payload=['tracking_number'=>$data['tracking_number']??($shipment->tracking_number??null),'carrier'=>$data['carrier']??($shipment->carrier??null),'service'=>$data['service']??($shipment->service??null),'status'=>$data['status']==='delivered'?'delivered':($shipment->status??'pending'),'updated_at'=>now()];
    if($shipment) DB::table('shipments')->where('id',$shipment->id)->update($payload);
    else DB::table('shipments')->insert(array_merge(['order_id'=>$order,'created_at'=>now()],$payload));
   }
   if($old->status!==$data['status']) app(NotificationService::class)->orderStatus((int)$old->user_id,$old->order_number,$data['status'],route('customer.orders.show',$order));
   if(($data['tracking_number']??null)!==null && ($data['tracking_number']??null)!==($shipment->tracking_number??null)) app(NotificationService::class)->send((int)$old->user_id,'Tracking updated','Your order '.$old->order_number.' has a new tracking number: '.$data['tracking_number'].'.','shipping',route('customer.orders.show',$order));
  });
  return back()->with('success','Order updated and inventory/shipment data synchronized.');
 }
}