<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class WishlistController extends Controller
{
 public function index(){ $items=DB::table('wishlists')->join('products','products.id','=','wishlists.product_id')->where('wishlists.user_id',auth()->id())->where('products.status','active')->select('wishlists.id as wishlist_id','products.*')->latest('wishlists.id')->paginate(24); return view('customer.wishlist.index',compact('items')); }
 public function toggle(Request $r,int $product){$uid=auth()->id();$exists=DB::table('wishlists')->where('user_id',$uid)->where('product_id',$product)->exists();if($exists)DB::table('wishlists')->where('user_id',$uid)->where('product_id',$product)->delete();else DB::table('wishlists')->insert(['user_id'=>$uid,'product_id'=>$product,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success',$exists?'Removed from wishlist.':'Added to wishlist.');}
 public function remove(int $wishlist){DB::table('wishlists')->where('id',$wishlist)->where('user_id',auth()->id())->delete();return back()->with('success','Removed from wishlist.');}
}
