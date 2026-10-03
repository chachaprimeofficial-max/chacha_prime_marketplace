@extends('layouts.storefront')
@section('content')
<div class="mx-auto max-w-md py-16"><div class="rounded-3xl border bg-white p-8 shadow-sm"><h1 class="text-3xl font-bold">Welcome back</h1><p class="mt-2 text-slate-500">Sign in to your Chacha Prime account.</p>
@if(session('two_factor_required'))<div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm">Enter the 6-digit code from your authenticator app.</div>@endif
@if($errors->any())<div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $e)<div>{{$e}}</div>@endforeach</div>@endif
<form method="POST" action="{{route('login')}}" class="mt-6 space-y-4">@csrf
<label class="block text-sm font-semibold">Email<input type="email" name="email" value="{{old('email')}}" required class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Password<input type="password" name="password" required class="mt-2 w-full rounded-xl border p-3"></label>
@if(session('two_factor_required'))<label class="block text-sm font-semibold">Authenticator code<input inputmode="numeric" maxlength="6" name="code" required class="mt-2 w-full rounded-xl border p-3 tracking-[0.35em]"></label>@endif
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Remember me</label>
<button class="w-full rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Sign in</button></form>
<div class="mt-5 flex justify-between text-sm"><a class="font-semibold" href="{{route('register')}}">Create account</a><a class="font-semibold" href="{{route('password.request')}}">Forgot password?</a></div>
</div></div>
@endsection