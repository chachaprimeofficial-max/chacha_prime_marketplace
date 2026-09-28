<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReturnController extends Controller
{
 public function index(){return view('admin.returns.index',['returns'=>DB::table('returns')->latest()->paginate(25)]);}
 public function approve(int $return){DB::transaction(function()use($return){$r=DB::table('returns')->where('id',$return)->lockForUpdate()->first();abort_unless($r&&$r->status==='requested',422,'Return is not awaiting approval.');DB::table('returns')->where('id',$return)->update(['status'=>'approved','updated_at'=>now()]);});return back()->with('success','Return approved.');}
 public function reject(Request $request,int $return){$reason=$request->validate(['admin_note'=>'required|string|max:1000'])['admin_note'];DB::table('returns')->where('id',$return)->where('status','requested')->update(['status'=>'rejected','admin_note'=>$reason,'updated_at'=>now()]);return back()->with('success','Return rejected.');}
 public function refund(int $return,WalletService $wallet){DB::transaction(function()use($return,$wallet){$r=DB::table('returns')->where('id',$return)->lockForUpdate()->first();abort_unless($r&&$r->status==='approved',422,'Return is not approved.');$item=DB::table('order_items')->where('id',$r->order_item_id)->first();if($item){app(InventoryService::class)->restore((int)$item->product_id,$item->variation_id?((int)$item->variation_id):null,(int)$item->quantity,'return','return',$r->id,auth()->id());}$wallet->credit((int)$r->user_id,(float)$r->refund_amount,'refund','Refund for return #'.$r->id,'return',$r->id);DB::table('returns')->where('id',$return)->update(['status'=>'refunded','refunded_at'=>now(),'updated_at'=>now()]);});return back()->with('success','Return completed: inventory restored and refund credited to Chacha Wallet.');}
}
