@extends('layouts.storefront')
@section('content')
<div class="mx-auto max-w-md py-16"><div class="rounded-3xl border bg-white p-8 shadow-sm"><h1 class="text-3xl font-bold">Reset password</h1><p class="mt-2 text-slate-500">Enter your account email to request reset instructions.</p>
@if(session('status'))<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{session('status')}}</div>@endif
@if($errors->any())<div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $e)<div>{{$e}}</div>@endforeach</div>@endif
<form method="POST" action="{{route('password.email')}}" class="mt-6 space-y-4">@csrf<label class="block text-sm font-semibold">Email<input type="email" name="email" required class="mt-2 w-full rounded-xl border p-3"></label><button class="w-full rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Request reset</button></form>
</div></div>
@endsection