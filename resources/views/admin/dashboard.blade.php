@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Chacha Prime Control Center</p><h1 class="mt-1 text-3xl font-bold">Admin Dashboard</h1></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Orders Today',$stats['orders_today']],['Pending Orders',$stats['pending_orders']],['Products',$stats['products']],['Customers',$stats['customers']],['Open Returns',$stats['open_returns']],['Active Groups',$stats['active_groups']],['Revenue Today',number_format($stats['revenue_today'],2)],
        ] as $card)
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">{{ $card[0] }}</p><strong class="mt-2 block text-2xl">{{ $card[1] }}</strong></div>
        @endforeach
    </div>
    <div class="grid gap-6 lg:grid-cols-[1fr_280px]">
        <div class="rounded-3xl border bg-white p-6 shadow-sm"><div class="flex justify-between"><h2 class="text-xl font-bold">Recent Orders</h2><a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-600">Manage</a></div><div class="mt-4 divide-y">@forelse($recentOrders as $order)<div class="flex items-center justify-between py-4"><div><strong>{{ $order->order_number }}</strong><p class="text-sm text-slate-500">{{ ucfirst(str_replace('_',' ',$order->status)) }}</p></div><strong>{{ number_format($order->total_amount,2) }} {{ $order->currency }}</strong></div>@empty<p class="py-8 text-center text-slate-500">No orders yet.</p>@endforelse</div></div>
        <nav class="rounded-3xl border bg-white p-5 shadow-sm"><h2 class="font-bold">Management</h2><div class="mt-4 grid gap-2 text-sm"><a href="{{ route('admin.products.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Products</a><a href="{{ route('admin.categories.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Categories & Mega Menu</a><a href="{{ route('admin.orders.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Orders & Shipping</a><a href="{{ route('admin.returns.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Returns</a><a href="{{ route('admin.group-buy.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Group Buying</a><a href="{{ route('admin.customers.index') }}" class="rounded-xl bg-slate-50 px-4 py-3">Customers</a></div></nav>
    </div>
</div>
@endsection
