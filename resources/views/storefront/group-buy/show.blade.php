@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="grid gap-8 lg:grid-cols-[1fr_460px]">
        <div class="rounded-3xl border bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Group Buying</p>
            <h1 class="mt-3 text-3xl font-bold">{{ $campaign->title }}</h1>
            <p class="mt-3 text-slate-600">Join other buyers to unlock the group price.</p>
            <div class="mt-8 rounded-2xl bg-orange-50 p-5">
                <div class="flex items-end justify-between gap-4">
                    <div><p class="text-sm text-slate-500">Group price</p><strong class="text-3xl">{{ number_format($campaign->group_price, 2) }} {{ $campaign->currency }}</strong></div>
                    <div class="text-right"><p class="text-sm text-slate-500">Buyers</p><strong class="text-2xl">{{ $campaign->buyer_count }} / {{ $campaign->required_buyers }}</strong></div>
                </div>
                <div class="mt-5 h-3 overflow-hidden rounded-full bg-white"><div class="h-full rounded-full bg-orange-500" style="width: {{ min(100, ($campaign->buyer_count / max(1, $campaign->required_buyers)) * 100) }}%"></div></div>
                <p class="mt-3 text-sm text-slate-500">Ends {{ $campaign->ends_at }}</p>
            </div>
        </div>
        <aside class="h-fit rounded-3xl border bg-slate-950 p-6 text-white">
            <h2 class="text-xl font-bold">Join this group</h2>
            <p class="mt-2 text-sm text-slate-300">Payment is collected immediately. If the required buyer condition is not reached before the campaign ends, the paid amount is automatically returned to your Chacha Wallet.</p>
            <form method="POST" action="{{ route('group-buy.join', $campaign) }}" class="mt-6">
                @csrf
                <label class="text-sm text-slate-300">Quantity</label>
                <input name="quantity" type="number" min="1" value="1" class="mt-2 w-full rounded-xl px-4 py-3 text-slate-950">
                <button class="mt-4 w-full rounded-2xl bg-white px-5 py-4 font-bold text-slate-950">Join & Pay</button>
            </form>
        </aside>
    </div>
</section>
@endsection
