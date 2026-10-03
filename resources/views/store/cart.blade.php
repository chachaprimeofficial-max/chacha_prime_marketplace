@extends('layouts.storefront')
@section('title','Cart | Chacha Prime')
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
<div class="mb-8"><p class="text-xs font-bold uppercase tracking-[.25em] text-blue-600">Chacha Prime</p><h1 class="mt-2 text-3xl font-black md:text-4xl">Shopping Cart</h1><p class="mt-2 text-slate-500">Review your items before secure checkout.</p></div>
@if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{session('success')}}</div>@endif
@if(empty($cart['items']))
<div class="rounded-[2rem] border bg-white p-14 text-center shadow-sm"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100"><span class="text-sm font-black text-slate-500">CART</span></div><h2 class="mt-5 text-xl font-black">Your cart is empty</h2><p class="mt-2 text-sm text-slate-500">Add products from the catalog to begin.</p><a href="{{route('products.index')}}" class="mt-6 inline-flex rounded-2xl bg-slate-950 px-6 py-3 font-bold text-white">Continue shopping</a></div>
@else
<div class="grid gap-6 lg:grid-cols-[1fr_360px]">
<section class="rounded-3xl border bg-white shadow-sm">
<form method="POST" action="{{route('cart.update')}}">@csrf
<div class="flex items-center justify-between border-b px-6 py-5"><h2 class="font-black">{{$cart['count']}} item(s)</h2><a href="{{route('products.index')}}" class="text-sm font-bold text-blue-600">Continue shopping</a></div>
<div class="divide-y">
@foreach($cart['items'] as $item)
<div class="flex gap-4 p-6">
<div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-slate-100">@if($item['product']->media->first())<img src="{{asset($item['product']->media->first()->path)}}" class="h-full w-full object-cover" alt="">@endif</div>
<div class="min-w-0 flex-1"><a href="{{route('products.show',$item['product']->slug)}}" class="font-bold hover:text-blue-600">{{$item['product']->title}}</a>@if($item['variant'])<p class="mt-1 text-xs text-slate-500">SKU: {{$item['variant']->sku}}</p>@endif<p class="mt-2 font-semibold">{{number_format($item['price'],2)}} {{config('chacha.brand.default_currency')}}</p></div>
<div class="flex flex-col items-end gap-3"><input type="number" min="0" max="99" name="items[{{$item['key']}}][quantity]" value="{{$item['quantity']}}" class="w-20 rounded-xl border px-3 py-2 text-center"><input type="hidden" name="items[{{$item['key']}}][key]" value="{{$item['key']}}"><strong>{{number_format($item['line_total'],2)}} {{config('chacha.brand.default_currency')}}</strong><a href="{{route('cart.remove',$item['key'])}}" class="text-xs font-bold text-red-600">Remove</a></div>
</div>
@endforeach
</div><div class="border-t px-6 py-4"><button class="rounded-xl border px-5 py-3 text-sm font-bold">Update cart</button></div>
</form>
</section>
<aside class="h-fit rounded-3xl bg-slate-950 p-6 text-white shadow-xl"><h2 class="text-lg font-black">Order summary</h2><div class="mt-6 space-y-3 text-sm"><div class="flex justify-between text-slate-300"><span>Subtotal</span><span>{{number_format($cart['subtotal'],2)}} {{config('chacha.brand.default_currency')}}</span></div><div class="flex justify-between text-slate-300"><span>Shipping</span><span>At checkout</span></div><div class="border-t border-white/10 pt-4"><div class="flex justify-between text-xl font-black"><span>Total</span><span>{{number_format($cart['total'],2)}} {{config('chacha.brand.default_currency')}}</span></div></div></div><a href="{{route('checkout.index')}}" class="mt-7 block rounded-2xl bg-white px-5 py-4 text-center font-black text-slate-950">Proceed to checkout</a></aside>
</div>
@endif
</section>
@endsection