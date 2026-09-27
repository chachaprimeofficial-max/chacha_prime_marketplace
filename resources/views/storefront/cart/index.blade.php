@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Chacha Prime</p>
        <h1 class="mt-2 text-3xl font-bold">Shopping Cart</h1>
    </div>
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="rounded-3xl border bg-white p-5 shadow-sm">
            @forelse($items as $item)
                <div class="flex gap-4 border-b py-5 last:border-0">
                    <div class="h-24 w-24 rounded-2xl bg-slate-100"></div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold">{{ $item['title'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">SKU: {{ $item['sku'] }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="font-bold">{{ number_format($item['price'], 2) }} {{ config('chacha.brand.default_currency') }}</span>
                            <span class="rounded-lg bg-slate-100 px-3 py-1 text-sm">Qty {{ $item['quantity'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-500">Your cart is empty.</div>
            @endforelse
        </div>
        <aside class="h-fit rounded-3xl border bg-slate-950 p-6 text-white">
            <h2 class="text-xl font-bold">Order Summary</h2>
            <div class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><strong>{{ number_format($totals['subtotal'] ?? 0, 2) }}</strong></div>
                <div class="flex justify-between"><span>Shipping</span><strong>{{ number_format($totals['shipping'] ?? 0, 2) }}</strong></div>
                <div class="flex justify-between"><span>Discount</span><strong>-{{ number_format($totals['discount'] ?? 0, 2) }}</strong></div>
                <div class="border-t border-white/20 pt-4 flex justify-between text-lg"><span>Total</span><strong>{{ number_format($totals['total'] ?? 0, 2) }} {{ config('chacha.brand.default_currency') }}</strong></div>
            </div>
            <a href="{{ route('checkout.index') }}" class="mt-6 block rounded-2xl bg-white px-5 py-4 text-center font-bold text-slate-950">Proceed to Checkout</a>
        </aside>
    </div>
</section>
@endsection
