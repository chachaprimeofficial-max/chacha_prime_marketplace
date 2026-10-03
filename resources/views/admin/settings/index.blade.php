@extends('layouts.admin')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
<div><p class="text-xs font-bold uppercase tracking-[.2em] text-blue-600">Platform</p><h1 class="text-3xl font-black">Settings</h1><p class="mt-1 text-sm text-slate-500">Manage storefront, commerce and operational configuration.</p></div>
@if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{session('success')}}</div>@endif
<form method="POST" action="{{route('admin.settings.update')}}" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @method('PUT')
<div class="grid gap-5 md:grid-cols-2">
@foreach($settings as $s)
<label class="block"><span class="text-sm font-semibold">{{str_replace(['_','.'],' ',ucwords($s->setting_key,'_.'))}}</span><input name="settings[{{$s->setting_key}}]" value="{{is_string($s->setting_value)?$s->setting_value:''}}" class="mt-2 w-full rounded-xl border px-4 py-3" @if($s->setting_type==='boolean') placeholder="1 or 0" @endif><span class="mt-1 block text-xs text-slate-400">{{$s->setting_key}} · {{$s->setting_type}}</span></label>
@endforeach
</div>
<div class="mt-6 flex flex-wrap gap-3"><button class="rounded-xl bg-slate-950 px-5 py-3 font-bold text-white">Save Settings</button><a href="{{route('admin.settings.audit')}}" class="rounded-xl border bg-white px-5 py-3 font-semibold">View Audit Log</a></div>
</form></div>
@endsection