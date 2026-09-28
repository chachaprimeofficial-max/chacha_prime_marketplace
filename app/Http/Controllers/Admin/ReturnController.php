<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(){return view('admin.returns.index',['returns'=>DB::table('returns')->latest()->paginate(25)]);}
    public function approve(int $return){DB::table('returns')->where('id',$return)->where('status','requested')->update(['status'=>'approved','updated_at'=>now()]);return back()->with('success','Return approved.');}
    public function reject(Request $request,int $return){$reason=$request->validate(['admin_note'=>'required|string|max:1000'])['admin_note'];DB::table('returns')->where('id',$return)->where('status','requested')->update(['status'=>'rejected','admin_note'=>$reason,'updated_at'=>now()]);return back()->with('success','Return rejected.');}
    public function refund(int $return,WalletService $wallet){DB::transaction(function()use($return,$wallet){$r=DB::table('returns')->where('id',$return)->lockForUpdate()->first();abort_unless($r&&$r->status==='approved',422,'Return is not approved.');$wallet->credit((int)$r->user_id,(float)$r->refund_amount,'refund','Refund for return #'.$r->id,'return',$r->id);DB::table('returns')->where('id',$return)->update(['status'=>'refunded','refunded_at'=>now(),'updated_at'=>now()]);});return back()->with('success','Refund credited to customer wallet.');}
}
