@extends('layouts.storefront')
@section('title','Wallet | Chacha Prime')
@section('content')
<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-8">
        <p class="text-xs font-black uppercase tracking-[.25em] text-blue-600">My Account</p>
        <h1 class="mt-2 text-4xl font-black">Chacha Wallet</h1>
        <p class="mt-2 text-slate-500">Your available balance and complete wallet ledger.</p>
    </div>
    <section class="rounded-[2rem] bg-slate-950 p-7 text-white shadow-xl md:p-9">
        <p class="text-sm text-slate-400">Available balance</p>
        <div class="mt-2 text-4xl font-black">{{ number_format((float)($wallet->balance ?? 0), 2) }} {{ $wallet->currency ?? config('chacha.brand.default_currency','USD') }}</div>
        <p class="mt-3 text-sm text-slate-400">Refunds, credits, bonuses and wallet purchases are recorded in the ledger.</p>
    </section>
    <section class="mt-7 overflow-hidden rounded-3xl border bg-white shadow-sm">
        @if($ledger instanceof \Illuminate\Pagination\LengthAwarePaginator && $ledger->count())
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500"><tr><th class="p-4 font-semibold">Date</th><th class="p-4 font-semibold">Type</th><th class="p-4 font-semibold">Reason</th><th class="p-4 font-semibold">Description</th><th class="p-4 font-semibold">Amount</th><th class="p-4 font-semibold">Balance</th></tr></thead>
                    <tbody class="divide-y">
                    @foreach($ledger as $entry)
                        <tr><td class="p-4">{{ $entry->created_at }}</td><td class="p-4 font-semibold">{{ ucfirst($entry->entry_type) }}</td><td class="p-4">{{ str_replace('_',' ',ucfirst($entry->reason)) }}</td><td class="p-4 text-slate-600">{{ $entry->description }}</td><td class="p-4 font-bold">{{ $entry->entry_type === 'credit' ? '+' : '-' }}{{ number_format((float)$entry->amount,2) }}</td><td class="p-4 font-semibold">{{ number_format((float)$entry->balance_after,2) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t p-4">{{ $ledger->links() }}</div>
        @else
            <div class="p-14 text-center"><h2 class="text-xl font-black">No wallet transactions yet</h2><p class="mt-2 text-sm text-slate-500">Your wallet ledger will appear here after credits or purchases.</p></div>
        @endif
    </section>
</section>
@endsection
