@extends('layouts.storefront')

@section('content')
<section class="container mx-auto max-w-3xl px-4 py-10">
    <div class="rounded-3xl border bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">After-sales service</p>
        <h1 class="mt-2 text-3xl font-bold">Refund or replacement</h1>
        <p class="mt-3 text-slate-500">Submit your request within 7 business days after delivery. Product photos/videos and a return shipping label can be attached for review.</p>
        <form method="POST" action="{{ route('customer.returns.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <input name="order_id" required placeholder="Order ID" class="rounded-xl border px-4 py-3">
                <input name="order_item_id" required placeholder="Order item ID" class="rounded-xl border px-4 py-3">
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="rounded-2xl border p-4"><input type="radio" name="type" value="refund" required> <strong>Refund</strong></label>
                <label class="rounded-2xl border p-4"><input type="radio" name="type" value="replacement"> <strong>Replacement</strong></label>
            </div>
            <textarea name="reason" required rows="5" placeholder="Explain the issue" class="w-full rounded-xl border px-4 py-3"></textarea>
            <div><label class="font-semibold">Product photos/videos</label><input name="media[]" type="file" multiple accept="image/*,video/*" class="mt-2 block w-full rounded-xl border p-3"></div>
            <div><label class="font-semibold">Return shipping label</label><input name="shipping_label" type="file" accept=".pdf,image/*" class="mt-2 block w-full rounded-xl border p-3"></div>
            <button class="w-full rounded-2xl bg-slate-950 px-5 py-4 font-bold text-white">Submit Request</button>
        </form>
    </div>
</section>
@endsection
