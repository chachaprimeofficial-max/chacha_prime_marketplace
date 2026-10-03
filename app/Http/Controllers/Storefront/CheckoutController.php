<?php
namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\ShippingRateService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
 public function index(Request $request)
 {
  $session=$request->session()->get('chacha_cart',[]);
  abort_unless(count($session),404,'Your cart is empty.');
  $cart=$this->cart($request);
  $uid=(int)$request->user()->id;
  $addresses=DB::table('addresses')->where('user_id',$uid)->orderByDesc('is_default')->orderByDesc('id')->get();
  $country=strtoupper((string)$request->input('country_code',session('checkout_country_code','PK')));
  $quotes=[];
  try{$quotes=app(ShippingRateService::class)->quote($country,$cart['subtotal']);}catch(\Throwable $e){}
  return view('storefront.checkout',[
   'cart'=>$cart,'wallet'=>app(WalletService::class)->balance($uid),
   'shipping_quotes'=>$quotes,'country_code'=>$country,'addresses'=>$addresses
  ]);
 }

 public function place(Request $request)
 {
  $data=$request->validate([
   'shipping_address_id'=>'required|integer',
   'billing_address_id'=>'nullable|integer',
   'country_code'=>'required|string|size:2',
   'shipping_rate_id'=>'required|integer',
   'payment_method'=>'required|in:wallet,cod',
   'coupon_code'=>'nullable|string|max:80'
  ]);
  return DB::transaction(function()use($request,$data){
   $uid=(int)$request->user()->id;
   $shippingAddress=DB::table('addresses')->where('id',$data['shipping_address_id'])->where('user_id',$uid)->firstOrFail();
   $billingId=$data['billing_address_id']??$data['shipping_address_id'];
   DB::table('addresses')->where('id',$billingId)->where('user_id',$uid)->firstOrFail();

   $cart=$this->cart($request);
   if(!$cart['items']) throw new RuntimeException('Your cart is empty.');

   $discount=0;$coupon=null;
   if(!empty($data['coupon_code'])){
    $coupon=app(CouponService::class)->findValid($data['coupon_code'],$cart['subtotal']);
    $discount=app(CouponService::class)->discount($coupon,$cart['subtotal']);
   }
   $shipping=app(ShippingRateService::class)->calculate(strtoupper($data['country_code']),$cart['subtotal'],(int)$data['shipping_rate_id']);
   $total=max(0,$cart['subtotal']-$discount+(float)$shipping['amount']);
   $number='CP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));

   $orderId=DB::table('orders')->insertGetId([
    'order_number'=>$number,'user_id'=>$uid,'billing_address_id'=>$billingId,'shipping_address_id'=>$shippingAddress->id,
    'order_type'=>'retail','status'=>'pending','subtotal'=>$cart['subtotal'],'shipping_amount'=>$shipping['amount'],
    'discount_amount'=>$discount,'tax_amount'=>0,'wallet_amount'=>0,'total_amount'=>$total,
    'currency'=>config('chacha.brand.default_currency','USD'),'placed_at'=>now(),'created_at'=>now(),'updated_at'=>now()
   ]);

   $inventory=app(InventoryService::class);
   foreach($cart['items'] as $item){
    $inventory->deduct((int)$item['product']->id,$item['variant']->id??null,(int)$item['quantity'],'order',$orderId,$uid);
    DB::table('order_items')->insert([
     'order_id'=>$orderId,'product_id'=>$item['product']->id,'variant_id'=>$item['variant']->id??null,
     'sku'=>$item['variant']->sku??$item['product']->sku,'product_title'=>$item['product']->title,
     'quantity'=>$item['quantity'],'unit_price'=>$item['price'],'total_price'=>$item['line_total'],'created_at'=>now(),'updated_at'=>now()
    ]);
   }

   $shipmentId=DB::table('shipments')->insertGetId([
    'order_id'=>$orderId,'carrier'=>$shipping['carrier']??null,'service'=>$shipping['name']??null,'status'=>'pending',
    'estimated_delivery_date'=>now()->addDays((int)($shipping['estimated_days_max']??7))->toDateString(),
    'notes'=>'Shipping rate #'.$shipping['rate_id'],'created_at'=>now(),'updated_at'=>now()
   ]);
   DB::table('shipment_events')->insert([
    'shipment_id'=>$shipmentId,'status'=>'pending','message'=>'Shipment created','event_at'=>now(),'created_at'=>now()
   ]);

   if($coupon) app(CouponService::class)->consume((int)$coupon->id);

   if($data['payment_method']==='wallet'){
    app(WalletService::class)->debit($uid,$total,'purchase','Chacha Prime order '.$orderId,'order',$orderId);
    DB::table('orders')->where('id',$orderId)->update(['status'=>'confirmed','updated_at'=>now()]);
    DB::table('payments')->insert([
     'order_id'=>$orderId,'provider'=>'chacha_wallet','method'=>'wallet','amount'=>$total,
     'currency'=>config('chacha.brand.default_currency','USD'),'status'=>'paid','paid_at'=>now(),'created_at'=>now(),'updated_at'=>now()
    ]);
   }else{
    DB::table('payments')->insert([
     'order_id'=>$orderId,'provider'=>'cash_on_delivery','method'=>'cod','amount'=>$total,
     'currency'=>config('chacha.brand.default_currency','USD'),'status'=>'pending','created_at'=>now(),'updated_at'=>now()
    ]);
   }

   app(\App\Services\InvoiceService::class)->createForOrder($orderId);

   DB::table('order_status_history')->insert([
    'order_id'=>$orderId,'status'=>'pending','note'=>'Order placed','created_by'=>$uid,'created_at'=>now()
   ]);
   $request->session()->put('checkout_country_code',strtoupper($data['country_code']));
   $request->session()->forget('chacha_cart');
   app(NotificationService::class)->send($uid,'Order received','Your order '.$number.' has been received.','order',route('customer.orders.show',$orderId));
   return redirect()->route('checkout.success',$orderId);
  });
 }

 public function success(int $order)
 {
  $item=DB::table('orders')->where('id',$order)->where('user_id',auth()->id())->firstOrFail();
  return view('storefront.checkout-success',compact('item'));
 }

 private function cart(Request $request):array
 {
  $rows=[];$subtotal=0;
  foreach($request->session()->get('chacha_cart',[]) as $key=>$row){
   $p=DB::table('products')->where('id',$row['product_id'])->where('status','active')->first();
   if(!$p)continue;
   $v=$row['variant_id']?DB::table('product_variants')->where('id',$row['variant_id'])->where('product_id',$p->id)->first():null;
   if(!empty($row['variant_id']) && !$v) continue;
   $price=(float)($v?->price ?? $p->retail_price);
   $line=$price*(int)$row['quantity'];$subtotal+=$line;
   $rows[]=['key'=>$key,'product'=>$p,'variant'=>$v,'quantity'=>(int)$row['quantity'],'price'=>$price,'line_total'=>$line];
  }
  return ['items'=>$rows,'subtotal'=>$subtotal,'shipping'=>0,'total'=>$subtotal,'count'=>array_sum(array_column($rows,'quantity'))];
 }
}
