<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WishlistReviewController extends Controller
{
 public function wishlist(Request $r){$items=DB::table('wishlists')->join('products','products.id','=','wishlists.product_id')->where('wishlists.user_id',$r->user()->id)->select('wishlists.id','products.id as product_id','products.title','products.slug','products.retail_price')->latest('wishlists.id')->paginate(20);return view('customer.wishlist',compact('items'));}
 public function add(Request $r,int $product){$r->validate(['product_id'=>'nullable']);DB::table('wishlists')->updateOrInsert(['user_id'=>$r->user()->id,'product_id'=>$product],['updated_at'=>now(),'created_at'=>now()]);return back()->with('success','Product added to wishlist.');}
 public function remove(Request $r,int $product){DB::table('wishlists')->where('user_id',$r->user()->id)->where('product_id',$product)->delete();return back()->with('success','Product removed from wishlist.');}
 public function storeReview(Request $r,int $product){$d=$r->validate(['rating'=>'required|integer|min:1|max:5','title'=>'nullable|string|max:120','body'=>'required|string|max:2000']);$p=DB::table('products')->where('id',$product)->firstOrFail();$verified=DB::table('orders')->join('order_items','orders.id','=','order_items.order_id')->where('orders.user_id',$r->user()->id)->where('order_items.product_id',$product)->whereIn('orders.status',['delivered','completed'])->exists();abort_unless($verified,403,'You can review products you purchased and received.');DB::table('product_reviews')->updateOrInsert(['user_id'=>$r->user()->id,'product_id'=>$product],['rating'=>$d['rating'],'title'=>$d['title']??null,'body'=>$d['body'],'status'=>'pending','verified_purchase'=>1,'updated_at'=>now(),'created_at'=>now()]);return back()->with('success','Review submitted for moderation.');}
}
