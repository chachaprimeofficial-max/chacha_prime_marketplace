<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class ReturnService
{
 public function create(int $userId,int $orderId,int $orderItemId,string $type,string $reason,array $media=[],?string $shippingLabel=null):int
 {
  if(!in_array($type,['refund','replacement'],true))throw new RuntimeException('Invalid return type.');
  $item=DB::table('order_items as oi')->join('orders as o','o.id','=','oi.order_id')->where('oi.id',$orderItemId)->where('oi.order_id',$orderId)->where('o.user_id',$userId)->first();
  if(!$item)throw new RuntimeException('Order item not found.');
  $order=DB::table('orders')->where('id',$orderId)->first();
  if(!$order||!$order->delivered_at)throw new RuntimeException('Order is not eligible for return.');
  $deadline=\Carbon\Carbon::parse($order->delivered_at)->addWeekdays(7);
  if(now()->greaterThan($deadline))throw new RuntimeException('Return window has expired.');
  $active=DB::table('returns')->where('order_item_id',$orderItemId)->whereIn('status',['requested','approved','in_transit','received'])->exists();if($active)throw new RuntimeException('A return is already in progress for this item.');
  $refundAmount=$type==='refund'?(float)$item->total_price:0;
  return DB::transaction(function()use($userId,$orderId,$orderItemId,$type,$reason,$media,$shippingLabel,$refundAmount){return DB::table('returns')->insertGetId(['user_id'=>$userId,'order_id'=>$orderId,'order_item_id'=>$orderItemId,'type'=>$type,'reason'=>$reason,'status'=>'requested','media_json'=>json_encode($media),'shipping_label_path'=>$shippingLabel,'refund_amount'=>$refundAmount,'created_at'=>now(),'updated_at'=>now()]);});
 }
}
