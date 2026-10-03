@extends('layouts.admin')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
<div><p class="text-xs font-bold uppercase tracking-[.2em] text-blue-600">Customer Care</p><h1 class="text-3xl font-black">Returns & Refunds</h1><p class="mt-1 text-slate-500">Review requests, receive returned items, restore stock and issue refunds.</p></div>
@if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{session('success')}}</div>@endif
<div class="overflow-x-auto rounded-3xl border bg-white shadow-sm"><table class="w-full min-w-[900px] text-left text-sm"><thead class="bg-slate-50"><tr><th class="p-4">ID</th><th>Order</th><th>Customer</th><th>Reason</th><th>Amount</th><th>Status</th><th>Requested</th><th class="p-4">Actions</th></tr></thead><tbody>
@forelse($returns as $r)
<tr class="border-t"><td class="p-4 font-bold">#{{$r->id}}</td><td>{{$r->order_number}}</td><td>{{$r->customer_name}}<div class="text-xs text-slate-500">{{$r->customer_email}}</div></td><td>{{Str::limit($r->reason,45)}}</td><td>{{number_format($r->refund_amount??0,2)}}</td><td><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{str_replace('_',' ',ucfirst($r->status))}}</span></td><td>{{$r->created_at}}</td>
<td class="p-4"><div class="flex flex-wrap gap-2">
@if($r->status==='requested')
<form method="POST" action="{{route('admin.returns.approve',$r->id)}}" class="flex gap-2">@csrf<select name="refund_method" class="rounded-lg border p-2"><option value="wallet">Chacha Wallet</option><option value="original_payment">Original Payment</option></select><button class="rounded-lg bg-slate-950 px-3 py-2 font-semibold text-white">Approve</button></form>
<form method="POST" action="{{route('admin.returns.reject',$r->id)}}">@csrf<input type="hidden" name="admin_note" value="Return request rejected by admin"><button class="rounded-lg border px-3 py-2 font-semibold text-red-600">Reject</button></form>
@elseif($r->status==='approved')
<form method="POST" action="{{route('admin.returns.receive',$r->id)}}">@csrf<button class="rounded-lg border px-3 py-2 font-semibold">Mark Received</button></form>
@elseif(in_array($r->status,['received','inspected']))
<form method="POST" action="{{route('admin.returns.refund',$r->id)}}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white">Refund & Restore Stock</button></form>
@elseif($r->status==='refunded')
<span class="rounded-lg bg-emerald-50 px-3 py-2 font-semibold text-emerald-700">Refunded</span>
@endif
</div></td></tr>
@empty<tr><td colspan="8" class="p-12 text-center text-slate-500">No return requests found.</td></tr>@endforelse
</tbody></table></div><div>{{$returns->links()}}</div></div>
@endsection
