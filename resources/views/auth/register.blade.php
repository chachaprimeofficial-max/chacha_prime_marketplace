@extends('layouts.storefront')
@section('content')
<div class="mx-auto max-w-lg py-16"><div class="rounded-3xl border bg-white p-8 shadow-sm"><h1 class="text-3xl font-bold">Create your account</h1><p class="mt-2 text-slate-500">Join Chacha Prime for retail, wholesale and group buying.</p>
@if($errors->any())<div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">@foreach($errors->all() as $e)<div>{{$e}}</div>@endforeach</div>@endif
<form method="POST" action="{{route('register')}}" class="mt-6 space-y-4">@csrf
<label class="block text-sm font-semibold">Full name<input name="name" value="{{old('name')}}" required class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Email<input type="email" name="email" value="{{old('email')}}" required class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Phone<input name="phone" value="{{old('phone')}}" class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Password<input type="password" name="password" required class="mt-2 w-full rounded-xl border p-3"></label>
<label class="block text-sm font-semibold">Confirm password<input type="password" name="password_confirmation" required class="mt-2 w-full rounded-xl border p-3"></label>
<button class="w-full rounded-2xl bg-slate-950 px-5 py-3 font-bold text-white">Create account</button></form>
<p class="mt-5 text-center text-sm">Already have an account? <a class="font-semibold" href="{{route('login')}}">Sign in</a></p>
</div></div>
@endsection