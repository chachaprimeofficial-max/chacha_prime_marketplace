@extends('layouts.storefront')
@section('content')
<div class="mx-auto max-w-md py-16"><div class="rounded-3xl border bg-white p-8 shadow-sm"><h1 class="text-3xl font-bold">Choose a new password</h1>
@if($errors->any())<div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $e)<div>{{$e}}</div>@endforeach</div>@endif
<form method="POST" action="{{route('password.update')}}" class="mt-6 space-y-4">@csrf
<input type="hidden" name="token" value="{{$token}}"><input type="hidden" name="email" value="{{$email}}">
<label class="block text-sm font-semibold">New password<input type="password" name="password" required class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Confirm password<input type="password" name="password_confirmation" required class="mt-2 w-full rounded-xl border p-3"></label>
<button class="w-full rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Update password</button></form></div></div>
@endsection