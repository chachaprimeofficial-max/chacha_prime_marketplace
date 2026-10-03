<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class OrderTrackingService {
 private const STATUSES=['pending','confirmed','processing','packing','shipped','in_transit','out_for_delivery','delivered','cancelled','returned','refunded'];
 public function update(int $orderId,string $status,?string $note=null,?int $userId=null):void{if(!in_array($status,self::STATUSES,true))throw new RuntimeException('Invalid order status.');DB::transaction(function()use($orderId,$status,$note,$userId){$order=DB::table('orders')->where('id',$orderId)->lockForUpdate()->firstOrFail();DB::table('orders')->where('id',$orderId)->update(['status'=>$status,'updated_at'=>now()]);DB::table('order_status_history')->insert(['order_id'=>$orderId,'status'=>$status,'note'=>$note,'created_by'=>$userId,'created_at'=>now()]);});}
}