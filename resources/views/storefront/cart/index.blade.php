@extends('layouts.storefront')
@section('title','Shopping Cart | Chacha Prime')
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
<div class="mb-8"><p class="text-xs font-black uppercase tracking-[.2em] text-blue-600">Chacha Prime</p><h1 class="mt-2 text-3xl font-black">Shopping Cart</h1></div>
@if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{session('success')}}</div>@endif
<form method="POST" action="{{route('cart.update')}}" class="grid gap-6 lg:grid-cols-[1fr_380px]">@csrf
<div class="rounded-3xl border bg-white p-5 shadow-sm">
@forelse($cart['items'] as $item)
<div class="flex gap-4 border-b py-5 last:border-0">
<div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-slate-100">
@if($item['product']->media->first() ?? false)<img src="{{asset($item['product']->media->first()->path)}}" alt="{{e($item['product']->title)}}" class="h-full w-full object-cover">@endif
</div>
<div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-bold">{{$item['product']->title}}</h2><p class="mt-1 text-sm text-slate-500">SKU: {{$item['variant']->sku ?? $item['product']->sku}}</p></div><button type="submit" form="remove-{{$loop->index}}" class="text-sm font-semibold text-red-600">Remove</button></div>
<div class="mt-4 flex flex-wrap items-center justify-between gap-3"><span class="font-black">{{number_format($item['price'],2)}} {{config('chacha.brand.default_currency')}}</span><label class="flex items-center gap-2 text-sm"><span>Qty</span><input name="items[{{$loop->index}}][key]" type="hidden" value="{{e($item['key'])}}"><input name="items[{{$loop->index}}][quantity]" type="number" min="0" max="99" value="{{$item['quantity']}}" class="w-20 rounded-xl border px-3 py-2 text-center"></label><strong>{{number_format($item['line_total'],2)}} {{config('chacha.brand.default_currency')}}</strong></div></div>
<form id="remove-{{$loop->index}}" method="POST" action="{{route('cart.remove',$item['key'])}}" class="hidden">@csrf</form>
</div>
@empty<div class="py-16 text-center"><h2 class="font-black">Your cart is empty</h2><p class="mt-2 text-sm text-slate-500">Add products to continue shopping.</p><a href="{{route('products.index')}}" class="mt-5 inline-block rounded-2xl bg-slate-950 px-6 py-3 font-bold text-white">Continue Shopping</a></div>@endforelse
@if($cart['items'])<button class="mt-5 rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Update Cart</button>@endif
</div>
<aside class="h-fit rounded-3xl bg-slate-950 p-6 text-white shadow-xl"><h2 class="text-xl font-black">Order Summary</h2><div class="mt-6 space-y-3 text-sm"><div class="flex justify-between"><span class="text-slate-300">Items</span><strong>{{$cart['count']}}</strong></div><div class="flex justify-between"><span class="text-slate-300">Subtotal</span><strong>{{number_format($cart['subtotal'],2)}} {{config('chacha.brand.default_currency')}}</strong></div><div class="flex justify-between"><span class="text-slate-300">Shipping</span><strong>Calculated at checkout</strong></div><div class="border-t border-white/20 pt-4 flex justify-between text-lg"><span>Total before shipping</span><strong>{{number_format($cart['total'],2)}} {{config('chacha.brand.default_currency')}}</strong></div></div>
@if($cart['items'])<a href="{{route('checkout.index')}}" class="mt-6 block rounded-2xl bg-white px-5 py-4 text-center font-black text-slate-950">Proceed to Checkout</a>@endif</aside>
</form></section>
@endsection