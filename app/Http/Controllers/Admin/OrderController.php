<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request){$q=DB::table('orders')->orderByDesc('id');if($request->filled('status'))$q->where('status',$request->status);if($request->filled('search'))$q->where('order_number','like','%'.$request->search.'%');return view('admin.orders.index',['orders'=>$q->paginate(25)->withQueryString()]);}
    public function show(int $order){$item=DB::table('orders')->where('id',$order)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();$payments=DB::table('payments')->where('order_id',$order)->latest()->get();return view('admin.orders.show',compact('item','items','payments'));}
    public function update(Request $request,int $order){$data=$request->validate(['status'=>'required|in:pending,confirmed,processing,shipped,delivered,completed,cancelled','tracking_number'=>'nullable|string|max:120','carrier'=>'nullable|string|max:120']);DB::table('orders')->where('id',$order)->update(array_merge($data,['updated_at'=>now()]));return back()->with('success','Order updated.');}
}
