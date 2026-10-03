<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SupportTicketController extends Controller {
 public function index(Request $r){$tickets=DB::table('support_tickets')->where('user_id',$r->user()->id)->latest()->paginate(15);return view('customer.support.index',compact('tickets'));}
 public function store(Request $r){$d=$r->validate(['subject'=>'required|string|max:255','priority'=>'required|in:low,normal,high,urgent','message'=>'required|string|max:5000']);$id=DB::transaction(function()use($d,$r){$id=DB::table('support_tickets')->insertGetId(['ticket_number'=>'CP-'.now()->format('YmdHis').'-'.random_int(100,999),'user_id'=>$r->user()->id,'subject'=>$d['subject'],'priority'=>$d['priority'],'status'=>'open','created_at'=>now(),'updated_at'=>now()]);DB::table('support_ticket_messages')->insert(['ticket_id'=>$id,'user_id'=>$r->user()->id,'message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);return $id;});return back()->with('success','Support ticket #'.$id.' created.');}
 public function show(Request $r,int $ticket){$ticket=DB::table('support_tickets')->where('id',$ticket)->where('user_id',$r->user()->id)->firstOrFail();$messages=DB::table('support_ticket_messages')->where('ticket_id',$ticket->id)->orderBy('created_at')->get();return view('customer.support.show',compact('ticket','messages'));}
 public function reply(Request $r,int $ticket){$ticket=DB::table('support_tickets')->where('id',$ticket)->where('user_id',$r->user()->id)->firstOrFail();$d=$r->validate(['message'=>'required|string|max:5000']);DB::table('support_ticket_messages')->insert(['ticket_id'=>$ticket->id,'user_id'=>$r->user()->id,'message'=>$d['message'],'created_at'=>now(),'updated_at'=>now()]);DB::table('support_tickets')->where('id',$ticket->id)->update(['status'=>'open','updated_at'=>now()]);return back()->with('success','Reply sent.');}
}