<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(count($request->session()->get('chacha_cart', [])), 404, 'Your cart is empty.');
        return view('storefront.checkout', ['cart'=>$this->cart($request), 'wallet'=>app(WalletService::class)->balance((int)$request->user()->id)]);
    }

    public function place(Request $request)
    {
        $data=$request->validate(['shipping_address'=>'required|string|max:3000','payment_method'=>'required|in:wallet,cod','coupon_code'=>'nullable|string|max:80']);
        return DB::transaction(function() use($request,$data){
            $cart=$this->cart($request); if(!$cart['items']) throw new RuntimeException('Your cart is empty.');
            $discount=0; $coupon=null;
            if(!empty($data['coupon_code'])){ $coupon=DB::table('coupons')->where('code',$data['coupon_code'])->where('status','active')->lockForUpdate()->first(); if(!$coupon || ($coupon->starts_at && now()->lt($coupon->starts_at)) || ($coupon->expires_at && now()->gt($coupon->expires_at)) || ($coupon->max_uses!==null && $coupon->used_count >= $coupon->max_uses) || $cart['subtotal'] < $coupon->min_order_amount) throw new RuntimeException('Coupon is not valid for this order.'); $discount=$coupon->type==='percent' ? round($cart['subtotal']*((float)$coupon->value/100),2) : min((float)$coupon->value,$cart['subtotal']); }
            $total=max(0,$cart['subtotal']-$discount+$cart['shipping']);
            $userId=(int)$request->user()->id;
            $orderId=DB::table('orders')->insertGetId(['user_id'=>$userId,'order_number'=>'CP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),'status'=>'pending','subtotal'=>$cart['subtotal'],'shipping_amount'=>$cart['shipping'],'discount_amount'=>$discount,'total_amount'=>$total,'currency'=>config('chacha.brand.default_currency','USD'),'payment_method'=>$data['payment_method'],'payment_status'=>'pending','shipping_address'=>$data['shipping_address'],'created_at'=>now(),'updated_at'=>now()]);
            foreach($cart['items'] as $item){$available=$item['variant']?(int)$item['variant']->stock_qty:(int)$item['product']->stock_qty;if($available<$item['quantity'])throw new RuntimeException('Stock changed for '.$item['product']->title.'.');DB::table('order_items')->insert(['order_id'=>$orderId,'product_id'=>$item['product']->id,'variation_id'=>$item['variant']->id??null,'sku'=>$item['variant']->sku??$item['product']->sku,'title'=>$item['product']->title,'quantity'=>$item['quantity'],'unit_price'=>$item['price'],'total_price'=>$item['line_total'],'created_at'=>now(),'updated_at'=>now()]);if($item['variant'])DB::table('product_variations')->where('id',$item['variant']->id)->decrement('stock_qty',$item['quantity']);else DB::table('products')->where('id',$item['product']->id)->decrement('stock_qty',$item['quantity']);}
            if($coupon)DB::table('coupons')->where('id',$coupon->id)->increment('used_count');
            if($data['payment_method']==='wallet'){app(WalletService::class)->debit($userId,$total,'purchase','Chacha Prime order '.$orderId,'order',$orderId);DB::table('orders')->where('id',$orderId)->update(['status'=>'confirmed','payment_status'=>'paid','updated_at'=>now()]);DB::table('payments')->insert(['order_id'=>$orderId,'user_id'=>$userId,'provider'=>'chacha_wallet','amount'=>$total,'currency'=>config('chacha.brand.default_currency','USD'),'status'=>'paid','created_at'=>now(),'updated_at'=>now()]);}
            else DB::table('payments')->insert(['order_id'=>$orderId,'user_id'=>$userId,'provider'=>'cash_on_delivery','amount'=>$total,'currency'=>config('chacha.brand.default_currency','USD'),'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
            $request->session()->forget('chacha_cart'); return redirect()->route('checkout.success',$orderId);
        });
    }
    public function success(int $order){$item=DB::table('orders')->where('id',$order)->where('user_id',auth()->id())->firstOrFail();return view('storefront.checkout-success',compact('item'));}
    private function cart(Request $request):array{$rows=[];$subtotal=0;foreach($request->session()->get('chacha_cart',[]) as $key=>$row){$p=DB::table('products')->where('id',$row['product_id'])->where('status','active')->first();if(!$p)continue;$v=$row['variant_id']?DB::table('product_variations')->where('id',$row['variant_id'])->first():null;$price=(float)($v->retail_price??$p->retail_price);$line=$price*(int)$row['quantity'];$subtotal+=$line;$rows[]=['key'=>$key,'product'=>$p,'variant'=>$v,'quantity'=>$row['quantity'],'price'=>$price,'line_total'=>$line];}return ['items'=>$rows,'subtotal'=>$subtotal,'shipping'=>0,'total'=>$subtotal];}
}
