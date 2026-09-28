<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return view('storefront.cart', ['cart' => $this->cart($request)]);
    }

    public function add(Request $request)
    {
        $data = $request->validate(['product_id'=>'required|integer','variant_id'=>'nullable|integer','quantity'=>'required|integer|min:1|max:99']);
        $product = Product::query()->where('id',$data['product_id'])->where('status','active')->firstOrFail();
        $variant = !empty($data['variant_id']) ? DB::table('product_variations')->where('id',$data['variant_id'])->where('product_id',$product->id)->first() : null;
        $available = $variant ? (int)$variant->stock_qty : (int)$product->stock_qty;
        abort_if($available < $data['quantity'], 422, 'Requested quantity is not available.');
        $cart = $request->session()->get('chacha_cart', []);
        $key = $product->id . ':' . ($variant->id ?? 0);
        $cart[$key] = ['product_id'=>$product->id,'variant_id'=>$variant->id ?? null,'quantity'=>min($available,($cart[$key]['quantity'] ?? 0)+(int)$data['quantity'])];
        $request->session()->put('chacha_cart',$cart);
        return redirect()->route('cart.index')->with('success','Product added to cart.');
    }

    public function update(Request $request)
    {
        $data = $request->validate(['items'=>'required|array','items.*.key'=>'required|string','items.*.quantity'=>'required|integer|min:0|max:99']);
        $cart=$request->session()->get('chacha_cart',[]);
        foreach($data['items'] as $item){ if(!isset($cart[$item['key']])) continue; if((int)$item['quantity']===0){unset($cart[$item['key']]);continue;} $row=$cart[$item['key']]; $stock=$row['variant_id'] ? (int)DB::table('product_variations')->where('id',$row['variant_id'])->value('stock_qty') : (int)DB::table('products')->where('id',$row['product_id'])->value('stock_qty'); $row['quantity']=min($stock,(int)$item['quantity']); $cart[$item['key']]=$row; }
        $request->session()->put('chacha_cart',$cart); return back()->with('success','Cart updated.');
    }

    public function remove(Request $request,string $key){$cart=$request->session()->get('chacha_cart',[]);unset($cart[$key]);$request->session()->put('chacha_cart',$cart);return back()->with('success','Item removed.');}

    private function cart(Request $request): array
    {
        $rows=[];$subtotal=0;$session=$request->session()->get('chacha_cart',[]);
        foreach($session as $key=>$row){$product=Product::query()->where('id',$row['product_id'])->where('status','active')->first();if(!$product)continue;$variant=$row['variant_id']?DB::table('product_variations')->where('id',$row['variant_id'])->first():null;$price=(float)($variant->retail_price ?? $product->retail_price);$line=$price*(int)$row['quantity'];$subtotal+=$line;$rows[]=['key'=>$key,'product'=>$product,'variant'=>$variant,'quantity'=>$row['quantity'],'price'=>$price,'line_total'=>$line];}
        return ['items'=>$rows,'subtotal'=>$subtotal,'count'=>array_sum(array_column($rows,'quantity')),'discount'=>0,'shipping'=>0,'total'=>$subtotal];
    }
}
