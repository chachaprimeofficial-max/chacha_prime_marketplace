<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class ShipmentService
{
 public function create(int $orderId,array $data):int{return DB::transaction(function()use($orderId,$data){$existing=DB::table('shipments')->where('order_id',$orderId)->whereIn('status',['pending','packed','dispatched','in_transit','out_for_delivery'])->first();if($existing)return (int)$existing->id;$id=DB::table('shipments')->insertGetId(['order_id'=>$orderId,'carrier'=>$data['carrier']??null,'service'=>$data['service']??null,'tracking_number'=>$data['tracking_number']??null,'status'=>'pending','estimated_delivery_date'=>$data['estimated_delivery_date']??null,'notes'=>$data['notes']??null,'created_at'=>now(),'updated_at'=>now()]);$this->event($id,'pending',null,'Shipment created');return $id;});}
 public function updateStatus(int $shipmentId,string $status,?string $location=null,?string $message=null):void{DB::transaction(function()use($shipmentId,$status,$location,$message){$s=DB::table('shipments')->where('id',$shipmentId)->lockForUpdate()->firstOrFail();$allowed=['pending','packed','dispatched','in_transit','out_for_delivery','delivered','returned','exception'];if(!in_array($status,$allowed,true))throw new RuntimeException('Invalid shipment status.');$update=['status'=>$status,'updated_at'=>now()];if($status==='dispatched'&&!$s->shipped_at)$update['shipped_at']=now();if($status==='delivered')$update['delivered_at']=now();DB::table('shipments')->where('id',$shipmentId)->update($update);$this->event($shipmentId,$status,$location,$message);});}
 private function event(int $shipmentId,string $status,?string $location,?string $message):void{DB::table('shipment_events')->insert(['shipment_id'=>$shipmentId,'status'=>$status,'location'=>$location,'message'=>$message,'event_at'=>now(),'created_at'=>now()]);}
}
