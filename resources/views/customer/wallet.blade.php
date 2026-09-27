@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="rounded-3xl bg-slate-950 p-7 text-white">
        <p class="text-sm uppercase tracking-[0.2em] text-blue-300">Chacha Wallet</p>
        <h1 class="mt-2 text-4xl font-bold">{{ number_format($wallet->balance ?? 0, 2) }} {{ $wallet->currency ?? config('chacha.brand.default_currency') }}</h1>
        <p class="mt-2 text-slate-300">Refunds, bonuses, credits and eligible purchases are recorded here.</p>
    </div>
    <div class="mt-6 rounded-3xl border bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold">Wallet activity</h2>
        <div class="mt-4 divide-y">
            @forelse($ledger as $entry)
                <div class="flex items-center justify-between gap-4 py-4"><div><strong>{{ ucfirst($entry->reason) }}</strong><p class="text-sm text-slate-500">{{ $entry->description }}</p></div><span class="font-bold {{ $entry->entry_type === 'credit' ? 'text-emerald-600' : 'text-slate-900' }}">{{ $entry->entry_type === 'credit' ? '+' : '-' }}{{ number_format($entry->amount, 2) }}</span></div>
            @empty
                <p class="py-8 text-center text-slate-500">No wallet activity yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
