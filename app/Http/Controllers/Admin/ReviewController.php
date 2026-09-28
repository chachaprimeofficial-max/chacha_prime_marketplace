<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReviewController extends Controller
{
 public function index(Request $r){$q=DB::table('product_reviews')->join('products','products.id','=','product_reviews.product_id')->join('users','users.id','=','product_reviews.user_id')->select('product_reviews.*','products.title as product_title','users.name as user_name')->latest('product_reviews.id');if($r->filled('status'))$q->where('product_reviews.status',$r->status);return view('admin.reviews.index',['reviews'=>$q->paginate(25)->withQueryString()]);}
 public function update(Request $r,int $review){$d=$r->validate(['status'=>'required|in:pending,approved,rejected']);DB::table('product_reviews')->where('id',$review)->update(['status'=>$d['status'],'moderated_at'=>now(),'updated_at'=>now()]);return back()->with('success','Review moderation updated.');}
 public function destroy(int $review){DB::table('product_reviews')->where('id',$review)->delete();return back()->with('success','Review removed.');}
}
