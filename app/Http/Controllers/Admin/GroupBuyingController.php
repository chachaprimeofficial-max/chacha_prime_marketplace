<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\GroupBuyingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class GroupBuyingController extends Controller
{
 public function index(){return view('admin.group-buying.index',['campaigns'=>DB::table('group_buying_campaigns as c')->join('products as p','p.id','=','c.product_id')->orderByDesc('c.id')->get(['c.*','p.title'])]);}
 public function store(Request $request){$data=$request->validate(['product_id'=>'required|integer','price'=>'required|numeric|min:0.01','required_buyers'=>'required|integer|min:2','starts_at'=>'nullable|date','expires_at'=>'required|date|after_or_equal:starts_at']);DB::table('group_buying_campaigns')->insert(['product_id'=>$data['product_id'],'price'=>$data['price'],'required_buyers'=>$data['required_buyers'],'starts_at'=>$data['starts_at']??now(),'expires_at'=>$data['expires_at'],'status'=>'active','created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Group-buy campaign created.');}
 public function finalize(int $campaign,GroupBuyingService $groups){$status=$groups->finalize($campaign);return back()->with('success','Campaign finalized: '.$status.'.');}
}