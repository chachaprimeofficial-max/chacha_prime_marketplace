@extends('layouts.storefront')
@section('title','Security | Chacha Prime')
@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
<div class="mb-8"><p class="text-xs font-black uppercase tracking-[.25em] text-blue-600">My Account</p><h1 class="mt-2 text-4xl font-black">Security</h1><p class="mt-2 text-slate-500">Protect your Chacha Prime account with a strong password and authenticator-based 2FA.</p></div>
@if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800">{{session('success')}}</div>@endif
<div class="grid gap-6 lg:grid-cols-2">
<section class="rounded-3xl border bg-white p-6 shadow-sm"><h2 class="text-xl font-black">Change password</h2><form method="POST" action="{{route('customer.security.password')}}" class="mt-6 space-y-4">@csrf @method('PUT')<input name="current_password" type="password" required placeholder="Current password" class="w-full rounded-2xl border p-4"><input name="password" type="password" required minlength="8" placeholder="New password" class="w-full rounded-2xl border p-4"><input name="password_confirmation" type="password" required minlength="8" placeholder="Confirm new password" class="w-full rounded-2xl border p-4"><button class="w-full rounded-2xl bg-slate-950 px-5 py-4 font-bold text-white">Update password</button></form></section>
<section class="rounded-3xl border bg-white p-6 shadow-sm"><h2 class="text-xl font-black">Authenticator 2FA</h2><p class="mt-2 text-sm leading-6 text-slate-500">Use Google Authenticator or another TOTP-compatible authenticator app.</p>
@if(!empty($twoFactorEnabled))
<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"><b class="text-emerald-800">Two-factor authentication is enabled.</b><p class="mt-1 text-xs text-emerald-700">Keep your recovery codes somewhere secure.</p></div>
<form method="POST" action="{{route('customer.security.2fa.disable')}}" class="mt-5 space-y-3">@csrf<input name="code" inputmode="numeric" maxlength="6" required placeholder="6-digit authenticator code" class="w-full rounded-2xl border p-4"><input name="current_password" type="password" required placeholder="Current password" class="w-full rounded-2xl border p-4"><button class="w-full rounded-2xl border border-red-200 px-5 py-4 font-bold text-red-700">Disable 2FA</button></form>
@else
<a href="{{route('customer.security.2fa.setup')}}" class="mt-5 block rounded-2xl bg-slate-950 px-5 py-4 text-center font-bold text-white">Set up authenticator 2FA</a>
@endif
</section></div>
</section>
@endsection