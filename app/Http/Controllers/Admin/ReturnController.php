<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReturnController extends Controller
{
 public function index(){return view('admin.returns.index',['returns'=>DB::table('returns')->latest()->paginate(25)]);}
 public function approve(Request $request,int $return,ReturnService $returns){$method=$request->validate(['refund_method'=>'nullable|in:wallet,original_payment'])['refund_method']??'wallet';$returns->approve($return,$method);return back()->with('success','Return approved.');}
 public function reject(Request $request,int $return,ReturnService $returns){$reason=$request->validate(['admin_note'=>'required|string|max:1000'])['admin_note'];$returns->reject($return,$reason);return back()->with('success','Return rejected.');}
 public function receive(int $return,ReturnService $returns){$returns->markReceived($return);return back()->with('success','Return marked received.');}
 public function refund(int $return,ReturnService $returns){$returns->refund($return);return back()->with('success','Return refund processed where supported.');}
}
