@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-8">
    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            <div class="overflow-hidden rounded-3xl bg-slate-100 aspect-square">
                @if($product->media->first())
                    <img id="main-product-image" src="{{ asset($product->media->first()->path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                @endif
            </div>
            @if($product->media->count() > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach($product->media as $media)
                        @if($media->type === 'image')
                            <button type="button" onclick="document.getElementById('main-product-image').src='{{ asset($media->path) }}'" class="overflow-hidden rounded-xl border bg-slate-50 aspect-square">
                                <img src="{{ asset($media->path) }}" alt="" class="h-full w-full object-cover">
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if($product->brand)
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-600">{{ $product->brand->name }}</p>
            @endif
            <h1 class="mt-2 text-3xl font-bold tracking-tight md:text-4xl">{{ $product->title }}</h1>
            <p class="mt-2 text-sm text-slate-500">SKU: {{ $product->sku }} · Product ID: {{ $product->product_code }}</p>

            <div class="mt-6 rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-end gap-3">
                    <span class="text-3xl font-extrabold">{{ $product->retail_price }} {{ config('chacha.brand.default_currency') }}</span>
                    @if($product->wholesale_price)
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">Wholesale {{ $product->wholesale_price }}</span>
                    @endif
                    @if($product->group_price)
                        <span class="rounded-full bg-orange-50 px-3 py-1 text-sm font-semibold text-orange-700">Group {{ $product->group_price }}</span>
                    @endif
                </div>
                @if($product->short_description)
                    <p class="mt-4 leading-7 text-slate-600">{{ $product->short_description }}</p>
                @endif
            </div>

            @if($product->highlights)
                <div class="mt-6">
                    <h2 class="font-bold">Highlights</h2>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($product->highlights as $highlight)
                            <li class="rounded-xl bg-slate-50 px-4 py-3 text-sm">✓ {{ is_array($highlight) ? json_encode($highlight) : $highlight }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($product->variants->count())
                <div class="mt-6">
                    <h2 class="font-bold">Variations</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($product->variants as $variant)
                            <button class="rounded-xl border px-4 py-2 text-sm hover:border-blue-500 hover:bg-blue-50">{{ collect($variant->attributes)->map(fn($v, $k) => $k . ': ' . $v)->implode(' · ') }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-7 flex gap-3">
                <button class="flex-1 rounded-2xl bg-slate-950 px-5 py-4 font-bold text-white hover:bg-slate-800">Add to Cart</button>
                <button class="rounded-2xl border px-5 py-4 font-bold">♡</button>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border p-4"><strong>Shipping</strong><p class="mt-1 text-sm text-slate-500">Economy shipping available. Express options shown at checkout.</p></div>
                <div class="rounded-2xl border p-4"><strong>Returns</strong><p class="mt-1 text-sm text-slate-500">{{ config('chacha.brand.return_window_business_days') }} business days after receipt.</p></div>
            </div>
        </div>
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold">Product details</h2>
            <div class="prose mt-4 max-w-none">{!! nl2br(e($product->description)) !!}</div>
        </div>
        <aside class="rounded-2xl border p-5">
            <h2 class="font-bold">Secure transaction</h2>
            <p class="mt-2 text-sm text-slate-500">Payment is processed through supported payment providers. Card details are not stored by Chacha Prime.</p>
            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm">
                <strong>Product QR</strong>
                <p class="mt-1 break-all text-slate-500">{{ $product->qr_value }}</p>
            </div>
        </aside>
    </div>
</section>
@endsection
