@extends('layouts.storefront')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Secure Checkout</p>
        <h1 class="mt-2 text-3xl font-bold">Complete your order</h1>
    </div>
    <form method="POST" action="{{ route('checkout.place') }}" class="grid gap-6 lg:grid-cols-[1fr_380px]">
        @csrf
        <div class="space-y-6">
            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold">Delivery address</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <input name="full_name" required placeholder="Full name" class="rounded-xl border px-4 py-3">
                    <input name="phone" required placeholder="Phone" class="rounded-xl border px-4 py-3">
                    <input name="address_line_1" required placeholder="Address" class="rounded-xl border px-4 py-3 md:col-span-2">
                    <input name="city" required placeholder="City" class="rounded-xl border px-4 py-3">
                    <input name="postal_code" placeholder="Postal code" class="rounded-xl border px-4 py-3">
                    <input name="country_code" required maxlength="2" placeholder="Country code" class="rounded-xl border px-4 py-3">
                </div>
            </div>
            <div class="rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold">Payment method</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach(['card' => 'Visa / Mastercard / Amex','paypal' => 'PayPal','alipay' => 'Alipay','wechat_pay' => 'WeChat Pay','easypaisa' => 'Easypaisa','jazzcash' => 'JazzCash','bank_transfer' => 'Bank Transfer','wallet' => 'Chacha Wallet'] as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border p-4 hover:border-blue-500">
                            <input type="radio" name="payment_method" value="{{ $value }}" required>
                            <span class="font-semibold">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-slate-500">Payment details are handled by the selected provider. Chacha Prime does not store raw card numbers.</p>
            </div>
        </div>
        <aside class="h-fit rounded-3xl border bg-slate-950 p-6 text-white">
            <h2 class="text-xl font-bold">Review & Pay</h2>
            <div class="mt-6 flex justify-between border-b border-white/20 pb-4"><span>Total</span><strong class="text-2xl">{{ number_format($totals['total'] ?? 0, 2) }} {{ config('chacha.brand.default_currency') }}</strong></div>
            <label class="mt-5 flex items-start gap-3 text-sm text-slate-300"><input type="checkbox" required class="mt-1"><span>I confirm my delivery details and agree to the applicable return and payment terms.</span></label>
            <button class="mt-6 w-full rounded-2xl bg-white px-5 py-4 font-bold text-slate-950">Place Order & Pay</button>
        </aside>
    </form>
</section>
@endsection
