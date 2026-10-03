<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
 public function createForOrder(int $orderId):int
 {
  return DB::transaction(function()use($orderId){
   $order=DB::table('orders')->where('id',$orderId)->lockForUpdate()->firstOrFail();
   $existing=DB::table('invoices')->where('order_id',$orderId)->value('id');
   if($existing)return (int)$existing;
   $invoiceNumber='CP-INV-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
   $payment=DB::table('payments')->where('order_id',$orderId)->latest('id')->first();
   $billing=$order->billing_address_id?DB::table('addresses')->where('id',$order->billing_address_id)->first():null;
   $shipping=$order->shipping_address_id?DB::table('addresses')->where('id',$order->shipping_address_id)->first():null;
   $invoiceId=DB::table('invoices')->insertGetId([
    'order_id'=>$orderId,'invoice_number'=>$invoiceNumber,'subtotal'=>$order->subtotal,'tax_amount'=>$order->tax_amount,
    'shipping_amount'=>$order->shipping_amount,'discount_amount'=>$order->discount_amount,'total_amount'=>$order->total_amount,
    'currency'=>$order->currency,'billing_address'=>$billing?json_encode($billing):null,'shipping_address'=>$shipping?json_encode($shipping):null,
    'payment_method'=>$payment?->method,'qr_payload'=>url('/qr/invoice/'.$orderId.'?ref='.rawurlencode($invoiceNumber)),
    'issued_at'=>now(),'created_at'=>now(),'updated_at'=>now()
   ]);
   foreach(DB::table('order_items')->where('order_id',$orderId)->get() as $item)
    DB::table('invoice_items')->insert([
     'invoice_id'=>$invoiceId,'product_id'=>$item->product_id,'title'=>$item->product_title,'sku'=>$item->sku,
     'quantity'=>$item->quantity,'unit_price'=>$item->unit_price,'tax_amount'=>0,'total_amount'=>$item->total_price,
     'created_at'=>now(),'updated_at'=>now()
    ]);
   return $invoiceId;
  });
 }
 public function data(int $orderId):array
 {
  $order=DB::table('orders')->where('id',$orderId)->firstOrFail();
  $invoice=DB::table('invoices')->where('order_id',$orderId)->firstOrFail();
  $items=DB::table('invoice_items')->where('invoice_id',$invoice->id)->get();
  return ['order'=>$order,'invoice'=>$invoice,'items'=>$items,'qr_payload'=>$invoice->qr_payload];
 }
}