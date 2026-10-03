<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\GroupBuyingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class GroupBuyingController extends Controller
{
 public function index(){return view('customer.group-buying.index',['campaigns'=>DB::table('group_buying_campaigns as c')->join('products as p','p.id','=','c.product_id')->where('c.status','active')->where('p.status','active')->orderBy('c.end_at')->get(['c.*','p.title','p.slug','p.group_buying_group_price'])]);}
 public function join(Request $request,int $campaign,GroupBuyingService $groups){$id=$groups->join((int)$request->user()->id,$campaign);return redirect()->route('customer.group-buying.show',$campaign)->with('success','Payment received and your group-buying participation is confirmed.');}
 public function show(int $campaign){$campaign=DB::table('group_buying_campaigns as c')->join('products as p','p.id','=','c.product_id')->where('c.id',$campaign)->first(['c.*','p.title','p.slug','p.group_buying_group_price']);abort_unless($campaign,404);$joined=DB::table('group_buying_orders')->where('campaign_id',$campaign->id)->where('user_id',auth()->id())->exists();return view('customer.group-buying.show',compact('campaign','joined'));}
}