@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"><div class="mb-8"><p class="text-xs font-bold uppercase tracking-[.25em] text-blue-600">Chacha Prime</p><h1 class="mt-2 text-3xl font-black">Wishlist</h1><p class="mt-1 text-slate-500">Save products you want to compare or buy later.</p></div><div class="rounded-3xl border bg-white p-6 shadow-sm"><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><div class="rounded-2xl border p-4"><div class="aspect-square rounded-xl bg-slate-100"></div><p class="mt-4 font-bold">Saved product</p><p class="mt-1 text-sm text-slate-500">$0.00</p><button class="mt-4 w-full rounded-xl bg-slate-950 px-4 py-3 text-sm font-bold text-white">Add to cart</button></div></div></div></div>
@endsection
