@extends('layouts.storefront')

@section('content')
<section class="container mx-auto max-w-3xl px-4 py-16 text-center">
    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-3xl">✓</div>
    <p class="mt-6 text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">Order confirmed</p>
    <h1 class="mt-2 text-4xl font-bold">Thank you for your order</h1>
    <p class="mx-auto mt-4 max-w-xl text-slate-500">Your payment has been submitted and your order is now being processed.</p>
    <div class="mx-auto mt-8 rounded-3xl border bg-white p-6 text-left shadow-sm">
        <div class="flex justify-between gap-4"><span class="text-slate-500">Order number</span><strong>{{ $order->order_number }}</strong></div>
        <div class="mt-3 flex justify-between gap-4"><span class="text-slate-500">Status</span><strong>{{ ucfirst($order->status) }}</strong></div>
        <div class="mt-3 flex justify-between gap-4"><span class="text-slate-500">Total</span><strong>{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</strong></div>
    </div>
    <a href="{{ route('products.index') }}" class="mt-8 inline-flex rounded-2xl bg-slate-950 px-6 py-3 font-bold text-white">Continue Shopping</a>
</section>
@endsection
