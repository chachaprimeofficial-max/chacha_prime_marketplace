<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\GroupBuyingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class GroupBuyingController extends Controller
{
 public function index(){return view('admin.group-buying.index',['campaigns'=>DB::table('group_buying_campaigns as c')->join('products as p','p.id','=','c.product_id')->orderByDesc('c.id')->get(['c.*','p.title','p.sku'])]);}
 public function store(Request $request){$d=$request->validate(['product_id'=>'required|integer|exists:products,id','variant_id'=>'nullable|integer','group_price'=>'required|numeric|min:0.01','minimum_buyers'=>'required|integer|min:2','maximum_buyers'=>'nullable|integer|min:2|gte:minimum_buyers','start_at'=>'required|date','end_at'=>'required|date|after:start_at']);$status=now()->lt($d['start_at'])?'scheduled':'active';DB::table('group_buying_campaigns')->insert(['product_id'=>$d['product_id'],'variant_id'=>$d['variant_id']??null,'group_price'=>$d['group_price'],'minimum_buyers'=>$d['minimum_buyers'],'maximum_buyers'=>$d['maximum_buyers']??null,'start_at'=>$d['start_at'],'end_at'=>$d['end_at'],'current_buyers'=>0,'status'=>$status,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Group-buy campaign created.');}
 public function finalize(int $campaign,GroupBuyingService $groups){$status=$groups->finalize($campaign);return back()->with('success','Campaign finalized: '.$status.'.');}
}