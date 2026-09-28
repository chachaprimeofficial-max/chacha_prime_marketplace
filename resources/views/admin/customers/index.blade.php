@extends('layouts.admin')
@section('content')
<div class="mx-auto max-w-7xl space-y-6">
<h1 class="text-3xl font-black">Customer Management</h1>
<form><input name="search" value="{{ request('search') }}" placeholder="Search customers" class="rounded-xl border px-4 py-3"><button class="rounded-xl bg-slate-950 px-5 py-3 text-white">Search</button></form>
<div class="overflow-hidden rounded-3xl border bg-white"><table class="w-full text-left text-sm"><thead><tr><th class="p-4">Customer</th><th class="p-4">Status</th><th class="p-4">2FA</th><th class="p-4">Action</th></tr></thead><tbody>
@foreach($customers as $c)<tr class="border-t"><td class="p-4"><b>{{ $c->name }}</b><div>{{ $c->email }}</div></td><td class="p-4">{{ ucfirst($c->status ?? 'active') }}</td><td class="p-4">{{ !empty($c->two_factor_enabled) ? 'Enabled' : 'Off' }}</td><td class="p-4"><a href="{{ route('admin.customers.show',$c->id) }}">View</a></td></tr>@endforeach
</tbody></table></div>{{ $customers->links() }}
</div>
@endsection
