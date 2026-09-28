<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CustomerController extends Controller
{
 public function index(Request $r){$q=DB::table('users')->select('users.*')->where(function($q){$q->whereNull('role')->orWhereNotIn('role',['admin','staff']);});if($r->filled('search'))$q->where(function($x)use($r){$x->where('name','like','%'.$r->search.'%')->orWhere('email','like','%'.$r->search.'%')->orWhere('phone','like','%'.$r->search.'%');});return view('admin.customers.index',['customers'=>$q->latest('id')->paginate(25)->withQueryString()]);}
 public function show(int $customer){$user=DB::table('users')->where('id',$customer)->firstOrFail();$orders=DB::table('orders')->where('user_id',$customer)->latest()->limit(20)->get();$wallet=DB::table('wallets')->where('user_id',$customer)->first();$returns=DB::table('returns')->where('user_id',$customer)->latest()->limit(20)->get();$reviews=DB::table('product_reviews')->where('user_id',$customer)->latest()->limit(20)->get();$addresses=DB::table('addresses')->where('user_id',$customer)->latest()->get();return view('admin.customers.show',compact('user','orders','wallet','returns','reviews','addresses'));}
 public function status(Request $r,int $customer){$d=$r->validate(['status'=>'required|in:active,suspended']);$user=DB::table('users')->where('id',$customer)->firstOrFail();abort_unless(!in_array($user->role??'', ['admin','staff']),403);DB::table('users')->where('id',$customer)->update(['status'=>$d['status'],'updated_at'=>now()]);return back()->with('success','Customer status updated.');}
}
