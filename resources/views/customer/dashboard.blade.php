@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="rounded-3xl bg-slate-950 p-7 text-white shadow-xl">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">My Chacha Prime</p>
        <h1 class="mt-2 text-3xl font-bold">Welcome back</h1>
        <p class="mt-2 text-slate-300">Manage orders, wallet, returns and account information from one place.</p>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('customer.orders') }}" class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md"><p class="text-sm text-slate-500">Recent orders</p><strong class="mt-2 block text-2xl">{{ $orders->count() }}</strong></a>
        <a href="{{ route('customer.wallet') }}" class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md"><p class="text-sm text-slate-500">Wallet balance</p><strong class="mt-2 block text-2xl">{{ number_format($wallet->balance ?? 0, 2) }}</strong></a>
        <a href="{{ route('customer.returns') }}" class="rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md"><p class="text-sm text-slate-500">Open returns</p><strong class="mt-2 block text-2xl">{{ $openReturns }}</strong></a>
    </div>
    <div class="mt-6 rounded-3xl border bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between"><h2 class="text-xl font-bold">Recent orders</h2><a class="text-sm font-semibold text-blue-600" href="{{ route('customer.orders') }}">View all</a></div>
        <div class="mt-5 divide-y">
            @forelse($orders as $order)
                <div class="flex items-center justify-between gap-4 py-4"><div><strong>{{ $order->order_number }}</strong><p class="text-sm text-slate-500">{{ ucfirst($order->status) }}</p></div><strong>{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</strong></div>
            @empty
                <p class="py-8 text-center text-slate-500">No orders yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
