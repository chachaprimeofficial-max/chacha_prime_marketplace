@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Chacha Prime</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">Discover products</h1>
        </div>
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search products" class="rounded-xl border px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500">
            <button class="rounded-xl bg-slate-950 px-5 py-2.5 font-semibold text-white">Search</button>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        @forelse($products as $product)
            <a href="{{ route('products.show', $product) }}" class="group overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                <div class="aspect-square bg-slate-100">
                    @if($product->media->first())
                        <img src="{{ asset($product->media->first()->path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @endif
                </div>
                <div class="p-4">
                    <p class="line-clamp-2 min-h-12 font-semibold">{{ $product->title }}</p>
                    <p class="mt-3 text-xl font-bold">{{ $product->retail_price }} {{ $product->currency ?? config('chacha.brand.default_currency') }}</p>
                    @if($product->wholesale_price)
                        <p class="mt-1 text-sm text-slate-500">Wholesale from {{ $product->wholesale_price }}</p>
                    @endif
                    @if($product->group_price)
                        <p class="mt-1 text-sm font-semibold text-orange-600">Group price {{ $product->group_price }}</p>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed p-12 text-center text-slate-500">No products found.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $products->links() }}</div>
</section>
@endsection
