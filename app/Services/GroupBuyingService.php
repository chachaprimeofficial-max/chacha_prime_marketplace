<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
class GroupBuyingService
{
 public function join(int $userId,int $campaignId):int{
  return DB::transaction(function()use($userId,$campaignId){
   $campaign=DB::table('group_buying_campaigns')->where('id',$campaignId)->lockForUpdate()->first();
   if(!$campaign||$campaign->status!=='active'||now()->lt($campaign->start_at)||now()->gt($campaign->end_at)) throw new RuntimeException('Group buying campaign is not available.');
   if((int)$campaign->current_buyers >= (int)$campaign->minimum_buyers) throw new RuntimeException('Group buying target has already been reached.');
   if($campaign->maximum_buyers!==null && (int)$campaign->current_buyers >= (int)$campaign->maximum_buyers) throw new RuntimeException('Group buying campaign is full.');
   if(DB::table('group_buying_orders')->where('campaign_id',$campaignId)->where('user_id',$userId)->whereIn('status',['pending','qualified'])->exists()) throw new RuntimeException('You have already joined this group.');
   $product=DB::table('products')->where('id',$campaign->product_id)->firstOrFail();
   $amount=(float)$campaign->group_price; if($amount<=0) throw new RuntimeException('Invalid group buying price.');
   $orderNumber='CP-GRP-'.strtoupper(Str::random(12));
   $orderId=DB::table('orders')->insertGetId(['order_number'=>$orderNumber,'user_id'=>$userId,'order_type'=>'group_buy','status'=>'pending','subtotal'=>$amount,'shipping_amount'=>0,'discount_amount'=>0,'tax_amount'=>0,'wallet_amount'=>$amount,'total_amount'=>$amount,'currency'=>config('chacha.brand.default_currency','USD'),'placed_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   DB::table('order_items')->insert(['order_id'=>$orderId,'product_id'=>$product->id,'variant_id'=>$campaign->variant_id,'sku'=>$product->sku,'product_title'=>$product->title,'quantity'=>1,'unit_price'=>$amount,'total_price'=>$amount,'created_at'=>now()]);
   $groupOrderId=DB::table('group_buying_orders')->insertGetId(['campaign_id'=>$campaignId,'order_id'=>$orderId,'user_id'=>$userId,'amount'=>$amount,'status'=>'pending','joined_at'=>now()]);
   app(WalletService::class)->debit($userId,$amount,'group_purchase','Immediate payment for group buying campaign #'.$campaignId,'group_buying_order',$groupOrderId);
   DB::table('payments')->insert(['order_id'=>$orderId,'provider'=>'wallet','method'=>'wallet','amount'=>$amount,'currency'=>config('chacha.brand.default_currency','USD'),'status'=>'paid','paid_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   app(InventoryService::class)->deduct((int)$product->id,$campaign->variant_id?(int)$campaign->variant_id:null,1,'group_buying',$groupOrderId,$userId);
   DB::table('group_buying_orders')->where('id',$groupOrderId)->update(['status'=>'qualified']);
   DB::table('group_buying_campaigns')->where('id',$campaignId)->increment('current_buyers');
   return $groupOrderId;
  });
 }
 public function finalize(int $campaignId):string{
  return DB::transaction(function()use($campaignId){
   $campaign=DB::table('group_buying_campaigns')->where('id',$campaignId)->lockForUpdate()->first();
   if(!$campaign||!in_array($campaign->status,['active','scheduled'])) throw new RuntimeException('Campaign cannot be finalized.');
   $met=(int)$campaign->current_buyers >= (int)$campaign->minimum_buyers;
   $participants=DB::table('group_buying_orders')->where('campaign_id',$campaignId)->where('status','qualified')->lockForUpdate()->get();
   if($met){
    DB::table('group_buying_campaigns')->where('id',$campaignId)->update(['status'=>'successful','updated_at'=>now()]);
    foreach($participants as $p) DB::table('orders')->where('id',$p->order_id)->update(['status'=>'confirmed','updated_at'=>now()]);
    return 'successful';
   }
   foreach($participants as $p){
    $orderItem=DB::table('order_items')->where('order_id',$p->order_id)->first();
    if($orderItem) app(InventoryService::class)->restore((int)$orderItem->product_id,$orderItem->variant_id?(int)$orderItem->variant_id:null,(int)$orderItem->quantity,'return','group_buying',$p->id,$p->user_id);
    app(WalletService::class)->credit((int)$p->user_id,(float)$p->amount,'group_refund','Group buying campaign #'.$campaignId.' did not reach its required buyer condition.','group_buying_order',(int)$p->id);
    DB::table('group_buying_orders')->where('id',$p->id)->update(['status'=>'refunded']);
    DB::table('orders')->where('id',$p->order_id)->update(['status'=>'refunded','updated_at'=>now()]);
    DB::table('payments')->where('order_id',$p->order_id)->update(['status'=>'refunded','updated_at'=>now()]);
   }
   DB::table('group_buying_campaigns')->where('id',$campaignId)->update(['status'=>'failed','updated_at'=>now()]);
   return 'refunded';
  });
 }
 public function failCampaign(int $campaignId):void{$this->finalize($campaignId);}
}