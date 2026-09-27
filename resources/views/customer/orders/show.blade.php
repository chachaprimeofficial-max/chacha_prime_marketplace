@extends('layouts.storefront')

@section('content')
<section class="container mx-auto max-w-5xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Order tracking</p><h1 class="mt-2 text-3xl font-bold">{{ $order->order_number }}</h1></div>
        <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
    </div>
    <div class="mt-8 rounded-3xl border bg-white p-6 shadow-sm">
        <div class="space-y-7">
            @php($steps = ['placed'=>'Order Placed','confirmed'=>'Confirmed','packing'=>'Packing','shipped'=>'Shipped','in_transit'=>'In Transit','delivered'=>'Delivered'])
            @foreach($steps as $key => $label)
                @php($done = $events->contains(fn($event) => $event->status === $key))
                <div class="flex gap-4">
                    <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $done ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-400' }}">{{ $done ? '✓' : '•' }}</div>
                    <div class="flex-1 border-b pb-5 last:border-0"><strong>{{ $label }}</strong>
                        @if($event = $events->where('status', $key)->last())
                            <p class="mt-1 text-sm text-slate-500">{{ $event->created_at }} @if($event->courier) · {{ $event->courier }} @endif @if($event->tracking_number) · {{ $event->tracking_number }} @endif</p>
                            @if($event->note)<p class="mt-1 text-sm text-slate-600">{{ $event->note }}</p>@endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
