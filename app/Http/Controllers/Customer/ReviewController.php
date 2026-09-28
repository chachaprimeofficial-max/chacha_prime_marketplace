<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReviewController extends Controller
{
 public function store(Request $r,int $product){$d=$r->validate(['rating'=>'required|integer|min:1|max:5','title'=>'nullable|string|max:150','body'=>'required|string|max:3000']);$uid=$r->user()->id;$purchased=DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->where('orders.user_id',$uid)->where('orders.status','completed')->where('order_items.product_id',$product)->exists();abort_unless($purchased,403,'You can review products you purchased.');DB::table('product_reviews')->updateOrInsert(['user_id'=>$uid,'product_id'=>$product],['rating'=>$d['rating'],'title'=>$d['title']??null,'body'=>$d['body'],'status'=>'pending','updated_at'=>now(),'created_at'=>now()]);return back()->with('success','Review submitted for moderation.');}
}
