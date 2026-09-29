<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class NotificationController extends Controller
{
 public function index(Request $request){$notifications=DB::table('notifications')->where('user_id',$request->user()->id)->latest('created_at')->paginate(20);$unread=(int)DB::table('notifications')->where('user_id',$request->user()->id)->whereNull('read_at')->count();return view('customer.notifications',compact('notifications','unread'));}
 public function read(Request $request,int $notification){$n=DB::table('notifications')->where('id',$notification)->where('user_id',$request->user()->id)->firstOrFail();DB::table('notifications')->where('id',$n->id)->update(['read_at'=>now()]);return back();}
 public function readAll(Request $request){DB::table('notifications')->where('user_id',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);return back()->with('success','All notifications marked as read.');}
}
