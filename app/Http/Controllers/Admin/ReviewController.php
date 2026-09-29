<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReviewController extends Controller
{
 public function index(Request $r){$q=DB::table('reviews')->join('products','products.id','=','reviews.product_id')->join('users','users.id','=','reviews.user_id')->select('reviews.*','products.title as product_title','users.name as user_name')->latest('reviews.id');if($r->filled('status'))$q->where('reviews.status',$r->status);return view('admin.reviews.index',['reviews'=>$q->paginate(25)->withQueryString()]);}
 public function update(Request $r,int $review){$d=$r->validate(['status'=>'required|in:pending,published,rejected']);DB::table('reviews')->where('id',$review)->update(['status'=>$d['status'],'updated_at'=>now()]);return back()->with('success','Review moderation updated.');}
 public function destroy(int $review){DB::table('reviews')->where('id',$review)->delete();return back()->with('success','Review removed.');}
}