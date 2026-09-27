<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class OrderWorkflowService
{
 public function create(int $userId,array $cart,array $shipping,array $payment): int
 {
  return DB::transaction(function()use($userId,$cart,$shipping,$payment){
   $subtotal=0;$items=[];
   foreach($cart as $row){$p=DB::table('products')->where('id',$row['product_id'])->lockForUpdate()->first();if(!$p||$p->status!=='active'||$p->stock_qty<$row['quantity'])throw new \RuntimeException('Product unavailable or insufficient stock.');$price=$row['unit_price']??$p->retail_price;$total=$price*$row['quantity'];$subtotal+=$total;$items[]=[$p,$row,$price,$total];}
   $shippingAmount=(float)($shipping['amount']??0);$total=$subtotal+$shippingAmount;
   $orderId=DB::table('orders')->insertGetId(['user_id'=>$userId,'order_number'=>'CP-'.strtoupper(Str::random(12)),'status'=>'confirmed','subtotal'=>$subtotal,'shipping_amount'=>$shippingAmount,'total_amount'=>$total,'currency'=>$payment['currency']??'USD','payment_method'=>$payment['method']??null,'payment_status'=>'paid','shipping_address'=>json_encode($shipping['address']??[]),'created_at'=>now(),'updated_at'=>now()]);
   foreach($items as [$p,$row,$price,$line]){DB::table('order_items')->insert(['order_id'=>$orderId,'product_id'=>$p->id,'variation_id'=>$row['variation_id']??null,'sku'=>$row['sku']??$p->sku,'title'=>$p->title,'quantity'=>$row['quantity'],'unit_price'=>$price,'total_price'=>$line,'created_at'=>now(),'updated_at'=>now()]);DB::table('products')->where('id',$p->id)->decrement('stock_qty',$row['quantity']);}
   DB::table('order_tracking_events')->insert(['order_id'=>$orderId,'status'=>'confirmed','created_at'=>now()]);
   DB::table('payments')->insert(['order_id'=>$orderId,'user_id'=>$userId,'provider'=>$payment['provider']??'wallet','transaction_reference'=>$payment['reference']??null,'amount'=>$total,'currency'=>$payment['currency']??'USD','status'=>'paid','created_at'=>now(),'updated_at'=>now()]);
   return $orderId;
  });
 }
}
