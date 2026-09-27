@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"><div class="mb-8"><p class="text-xs font-bold uppercase tracking-[.25em] text-blue-600">Chacha Prime</p><h1 class="mt-2 text-3xl font-black">All Categories</h1><p class="mt-1 text-slate-500">Browse the marketplace by category.</p></div><div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">@foreach(($categories ?? []) as $category)<a href="{{ url('/category/'.$category->slug) }}" class="rounded-3xl border bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><p class="font-black">{{ $category->name }}</p><p class="mt-2 text-sm text-slate-500">Explore products</p></a>@endforeach</div></div>
@endsection
