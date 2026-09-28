<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class NotificationController extends Controller
{
 public function index(Request $request){$notifications=DB::table('notifications')->where('user_id',$request->user()->id)->latest()->paginate(20);return view('customer.notifications',compact('notifications'));}
 public function read(Request $request,int $notification){$n=DB::table('notifications')->where('id',$notification)->where('user_id',$request->user()->id)->firstOrFail();DB::table('notifications')->where('id',$n->id)->update(['read_at'=>now()]);return back();}
 public function readAll(Request $request){DB::table('notifications')->where('user_id',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);return back()->with('success','All notifications marked as read.');}
}
