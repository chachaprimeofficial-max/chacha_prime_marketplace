@extends('layouts.storefront')
@section('title','Invoice '.$invoice->invoice_number.' | Chacha Prime')
@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-black uppercase tracking-[.25em] text-blue-600">Order Invoice</p><h1 class="mt-2 text-4xl font-black">{{ $invoice->invoice_number }}</h1><p class="mt-2 text-slate-500">Order {{ $invoice->order_number }}</p></div>
        <a href="{{ route('customer.invoices.print',$invoice->order_id) }}" target="_blank" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Print invoice</a>
    </div>
    <div class="mt-7 grid gap-6 lg:grid-cols-[1fr_220px]">
        <div class="rounded-3xl border bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-500">Billing</p><p class="mt-2 whitespace-pre-line text-sm">{{ $invoice->billing_address ?: 'Same as shipping address' }}</p></div>
                <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-500">Shipping</p><p class="mt-2 whitespace-pre-line text-sm">{{ $invoice->shipping_address ?: '—' }}</p></div>
            </div>
            <div class="mt-6 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="border-b bg-slate-50"><tr><th class="p-3">Item</th><th class="p-3">SKU</th><th class="p-3">Qty</th><th class="p-3">Unit</th><th class="p-3">Total</th></tr></thead><tbody class="divide-y">@foreach($items as $item)<tr><td class="p-3">{{ $item->title }}</td><td class="p-3">{{ $item->sku ?: '—' }}</td><td class="p-3">{{ $item->quantity }}</td><td class="p-3">{{ number_format($item->unit_price,2) }}</td><td class="p-3 font-bold">{{ number_format($item->total_amount,2) }}</td></tr>@endforeach</tbody></table></div>
            <div class="ml-auto mt-6 max-w-sm space-y-2 text-sm"><div class="flex justify-between"><span>Subtotal</span><b>{{ number_format($invoice->subtotal,2) }}</b></div><div class="flex justify-between"><span>Discount</span><b>-{{ number_format($invoice->discount_amount,2) }}</b></div><div class="flex justify-between"><span>Shipping</span><b>{{ number_format($invoice->shipping_amount,2) }}</b></div><div class="flex justify-between"><span>Tax</span><b>{{ number_format($invoice->tax_amount,2) }}</b></div><div class="flex justify-between border-t pt-3 text-lg"><span class="font-black">Total</span><b>{{ number_format($invoice->total_amount,2) }} {{ $invoice->currency }}</b></div></div>
        </div>
        <aside class="rounded-3xl border bg-white p-6 text-center shadow-sm"><p class="text-sm font-bold">Invoice QR</p><img class="mx-auto mt-4 h-40 w-40" src="{{ route('qr.show',['type'=>'invoice','id'=>$invoice->id]) }}" alt="Invoice QR"><p class="mt-4 break-all text-[10px] text-slate-400">{{ $invoice->qr_payload }}</p></aside>
    </div>
</section>
@endsection
