@extends('layouts.storefront')
@section('title','Shop | Chacha Prime')
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10">
<div class="rounded-[2rem] bg-slate-950 p-7 text-white md:p-10">
<p class="text-xs font-bold uppercase tracking-[.28em] text-blue-300">Chacha Prime Shop</p>
<h1 class="mt-2 text-3xl font-black md:text-5xl">Discover products</h1>
<p class="mt-3 max-w-2xl text-slate-300">Retail prices, verified wholesale options and group-buy opportunities in one catalog.</p>
<form method="GET" class="mt-7 grid gap-3 md:grid-cols-[1fr_auto]">
<input name="q" value="{{request('q')}}" placeholder="Search by product, SKU or product code" class="rounded-2xl border-0 px-5 py-4 text-slate-950 outline-none">
<button class="rounded-2xl bg-white px-7 py-4 font-black text-slate-950">Search</button>
</form>
</div>
<div class="mt-8 grid gap-7 lg:grid-cols-[250px_1fr]">
<aside class="h-fit rounded-3xl border bg-white p-5 shadow-sm">
<h2 class="font-black">Filters</h2>
<form method="GET" class="mt-5 space-y-4">
<input type="hidden" name="q" value="{{request('q')}}">
<label class="block text-sm font-semibold">Category<select name="category" class="mt-2 w-full rounded-xl border p-3"><option value="">All categories</option>@foreach($categories as $cat)<option value="{{$cat->slug}}" @selected(request('category')===$cat->slug)>{{$cat->name}}</option>@endforeach</select></label>
<div class="grid grid-cols-2 gap-2"><input name="min_price" value="{{request('min_price')}}" type="number" min="0" step="0.01" placeholder="Min" class="w-full rounded-xl border p-3"><input name="max_price" value="{{request('max_price')}}" type="number" min="0" step="0.01" placeholder="Max" class="w-full rounded-xl border p-3"></div>
<label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="in_stock" value="1" @checked(request('in_stock'))> In stock</label>
<select name="sort" class="w-full rounded-xl border p-3 text-sm"><option value="">Newest</option><option value="popular" @selected(request('sort')==='popular')>Most popular</option><option value="rating" @selected(request('sort')==='rating')>Top rated</option><option value="price_asc" @selected(request('sort')==='price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort')==='price_desc')>Price: high to low</option></select>
<button class="w-full rounded-xl bg-slate-950 px-4 py-3 font-bold text-white">Apply filters</button>
</form>
</aside>
<main>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">{{$products->total()}} products</p><div class="flex items-center gap-3"><label class="text-sm text-slate-500">Sort <select form="mobile-sort" class="rounded-xl border px-3 py-2 text-sm" onchange="document.getElementById('mobile-sort-value').value=this.value;document.getElementById('mobile-sort').submit()"><option value="">Newest</option><option value="popular" @selected(request('sort')==='popular')>Popular</option><option value="rating" @selected(request('sort')==='rating')>Top rated</option><option value="price_asc" @selected(request('sort')==='price_asc')>Price low</option><option value="price_desc" @selected(request('sort')==='price_desc')>Price high</option></select></label><a href="{{route('products.index')}}" class="text-sm font-bold text-blue-600">Clear</a></div></div><form id="mobile-sort" method="GET" class="hidden"><input type="hidden" name="q" value="{{request('q')}}"><input type="hidden" name="category" value="{{request('category')}}"><input type="hidden" name="min_price" value="{{request('min_price')}}"><input type="hidden" name="max_price" value="{{request('max_price')}}"><input type="hidden" name="in_stock" value="{{request('in_stock')}}"><input id="mobile-sort-value" type="hidden" name="sort" value="{{request('sort')}}"></form>
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
@forelse($products as $product)
<a href="{{route('products.show',$product->slug)}}" class="group overflow-hidden rounded-3xl border bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
<div class="relative aspect-square bg-slate-100">
@if($product->media->first())<img src="{{asset($product->media->first()->path)}}" alt="{{$product->title}}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">@else<div class="flex h-full items-center justify-center text-sm text-slate-400">Image coming soon</div>@endif
@if($product->stock_qty<1)<span class="absolute left-3 top-3 rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Out of stock</span>@endif
</div>
<div class="p-5"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{$product->brand->name ?? 'Chacha Prime'}}</p><h2 class="mt-1 line-clamp-2 min-h-12 font-bold">{{$product->title}}</h2><div class="mt-4 flex items-end justify-between gap-3"><strong class="text-xl">{{number_format($product->retail_price,2)}} {{config('chacha.brand.default_currency')}}</strong><span class="text-xs text-slate-500">{{$product->total_sold}} sold</span></div>@if($product->wholesale_price)<p class="mt-2 text-xs font-semibold text-blue-700">Wholesale available</p>@endif</div>
</a>
@empty<div class="rounded-3xl border bg-white p-14 text-center sm:col-span-2 xl:col-span-3"><h2 class="font-black">No matching products</h2><p class="mt-2 text-sm text-slate-500">Try a different search or remove some filters.</p></div>@endforelse
</div>
<div class="mt-8">{{$products->links()}}</div>
</main></div></section>
@endsection