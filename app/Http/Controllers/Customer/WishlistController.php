<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WishlistController extends Controller
{
 public function index(){ $items=DB::table('wishlist_items')->join('products','products.id','=','wishlist_items.product_id')->where('wishlist_items.user_id',auth()->id())->where('products.status','active')->select('wishlist_items.id as wishlist_id','products.*')->latest('wishlist_items.id')->paginate(24); return view('customer.wishlist.index',compact('items')); }
 public function toggle(Request $r,int $product){$uid=auth()->id();$exists=DB::table('wishlist_items')->where('user_id',$uid)->where('product_id',$product)->exists();if($exists)DB::table('wishlist_items')->where('user_id',$uid)->where('product_id',$product)->delete();else DB::table('wishlist_items')->insert(['user_id'=>$uid,'product_id'=>$product,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success',$exists?'Removed from wishlist.':'Added to wishlist.');}
 public function remove(int $wishlist){DB::table('wishlist_items')->where('id',$wishlist)->where('user_id',auth()->id())->delete();return back()->with('success','Removed from wishlist.');}
}
