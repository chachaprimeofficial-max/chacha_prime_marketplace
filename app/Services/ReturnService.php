<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class ReturnService
{
 public function create(int $userId,int $orderId,int $orderItemId,string $type,string $reason,array $media=[],?string $shippingLabel=null):int
 {
  if(!in_array($type,['refund','replacement'],true))throw new RuntimeException('Invalid return type.');
  return DB::transaction(function()use($userId,$orderId,$orderItemId,$type,$reason,$media,$shippingLabel){
   $item=DB::table('order_items as oi')->join('orders as o','o.id','=','oi.order_id')->where('oi.id',$orderItemId)->where('oi.order_id',$orderId)->where('o.user_id',$userId)->lockForUpdate()->first();
   if(!$item)throw new RuntimeException('Order item not found.');
   $order=DB::table('orders')->where('id',$orderId)->first();
   if(!$order||!$order->delivered_at)throw new RuntimeException('Order is not eligible for return.');
   $deadline=\Carbon\Carbon::parse($order->delivered_at)->addWeekdays(7)->endOfDay();
   if(now()->greaterThan($deadline))throw new RuntimeException('Return window has expired.');
   $active=DB::table('returns')->where('order_id',$orderId)->whereIn('status',['requested','approved','received'])->whereExists(function($q)use($orderItemId){$q->select(DB::raw(1))->from('return_items')->whereColumn('return_items.return_id','returns.id')->where('return_items.order_item_id',$orderItemId); })->exists();
   if($active)throw new RuntimeException('A return is already in progress for this item.');
   $returnId=DB::table('returns')->insertGetId(['user_id'=>$userId,'order_id'=>$orderId,'reason'=>$reason,'description'=>$reason,'evidence'=>json_encode(['media'=>$media,'shipping_label'=>$shippingLabel]),'status'=>'requested','refund_method'=>$type==='refund'?'wallet':null,'refund_amount'=>$type==='refund'?(float)$item->total_price:0,'requested_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   DB::table('return_items')->insert(['return_id'=>$returnId,'order_item_id'=>$orderItemId,'quantity'=>$item->quantity,'reason'=>$reason,'created_at'=>now(),'updated_at'=>now()]);
   return $returnId;
  });
 }
 public function approve(int $returnId,string $refundMethod='wallet'):void{if(!in_array($refundMethod,['wallet','original_payment'],true))throw new RuntimeException('Invalid refund method.');DB::transaction(function()use($returnId,$refundMethod){$r=DB::table('returns')->where('id',$returnId)->lockForUpdate()->firstOrFail();if($r->status!=='requested')throw new RuntimeException('Return is not awaiting approval.');DB::table('returns')->where('id',$returnId)->update(['status'=>'approved','refund_method'=>$refundMethod,'approved_at'=>now(),'updated_at'=>now()]);});}
 public function reject(int $returnId,string $reason=''):void{DB::table('returns')->where('id',$returnId)->where('status','requested')->update(['status'=>'rejected','description'=>$reason,'updated_at'=>now()]);}
 public function markReceived(int $returnId):void{DB::table('returns')->where('id',$returnId)->where('status','approved')->update(['status'=>'received','updated_at'=>now()]);}
 public function refund(int $returnId):void{DB::transaction(function()use($returnId){$r=DB::table('returns')->where('id',$returnId)->lockForUpdate()->firstOrFail();if(!in_array($r->status,['approved','received'],true)||!$r->refund_amount>0)throw new RuntimeException('Return is not ready for refund.');if(DB::table('refund_transactions')->where('return_id',$returnId)->where('status','processed')->exists())return;$method=$r->refund_method?:'wallet';$tx=DB::table('refund_transactions')->insertGetId(['return_id'=>$returnId,'method'=>$method,'amount'=>$r->refund_amount,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);if($method==='wallet'){app(WalletService::class)->credit((int)$r->user_id,(float)$r->refund_amount,'refund','Return refund','return',$returnId);DB::table('refund_transactions')->where('id',$tx)->update(['status'=>'processed','processed_at'=>now(),'updated_at'=>now()]);DB::table('returns')->where('id',$returnId)->update(['status'=>'refunded','refunded_at'=>now(),'updated_at'=>now()]);}else{DB::table('refund_transactions')->where('id',$tx)->update(['status'=>'pending','updated_at'=>now()]);}});}
}
