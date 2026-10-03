<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SupportTicketController extends Controller {
 public function index(){ $tickets=DB::table('support_tickets')->leftJoin('users','users.id','=','support_tickets.user_id')->select('support_tickets.*','users.name','users.email')->latest('support_tickets.id')->paginate(25);return view('admin/support/index',compact('tickets')); }
 public function show(int $ticket){$ticket=DB::table('support_tickets')->leftJoin('users','users.id','=','support_tickets.user_id')->select('support_tickets.*','users.name','users.email')->where('support_tickets.id',$ticket)->firstOrFail();$messages=DB::table('support_ticket_messages')->where('ticket_id',$ticket->id)->orderBy('created_at')->get();return view('admin/support/show',compact('ticket','messages'));}
 public function update(Request $r,int $ticket){$d=$r->validate(['status'=>'required|in:open,pending,resolved,closed','priority'=>'required|in:low,normal,high,urgent']);DB::table('support_tickets')->where('id',$ticket)->update(array_merge($d,['assigned_to'=>$r->user()->id,'updated_at'=>now()]));return back()->with('success','Ticket updated.');}
 public function reply(Request $r,int $ticket){$d=$r->validate(['message'=>'required|string|max:5000']);DB::table('support_ticket_messages')->insert(['ticket_id'=>$ticket,'user_id'=>$r->user()->id,'message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);DB::table('support_tickets')->where('id',$ticket)->update(['status'=>'pending','assigned_to'=>$r->user()->id,'updated_at'=>now()]);return back()->with('success','Reply sent.');}
}