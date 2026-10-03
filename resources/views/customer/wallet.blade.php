@extends('layouts.storefront')
@section('title','Wallet | Chacha Prime')
@section('content')

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Chacha Prime Wallet</title>
    <style>
        :root{--ink:#101828;--muted:#667085;--line:#eaecf0;--surface:#fff;--bg:#f7f8fa;--brand:#111827}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,system-ui,-apple-system,sans-serif}
        .wrap{max-width:1120px;margin:auto;padding:32px 20px}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .brand{font-weight:800;font-size:22px;letter-spacing:-.4px}.sub{color:var(--muted);font-size:14px}
        .balance{background:var(--brand);color:white;border-radius:24px;padding:28px 30px;margin-bottom:22px;box-shadow:0 12px 35px rgba(16,24,40,.14)}
        .balance small{opacity:.7}.amount{font-size:40px;font-weight:800;margin-top:8px;letter-spacing:-1px}
        .card{background:var(--surface);border:1px solid var(--line);border-radius:20px;overflow:hidden}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:15px 18px;border-bottom:1px solid var(--line);font-size:14px}
        th{color:var(--muted);font-weight:600;background:#fafafa}.credit{font-weight:700}.debit{font-weight:700}
        .empty{padding:40px;text-align:center;color:var(--muted)}
    </style>


<section class="mx-auto max-w-6xl px-4 py-10"><div class="wrap">
    <div class="top"><div><div class="brand">Chacha Prime</div><div class="sub">Wallet & transaction history</div></div></div>
    <section class="balance">
        <small>Available balance</small>
        <div class="amount">{{ number_format((float)($wallet->balance ?? 0), 2) }} {{ $wallet->currency ?? config('chacha.brand.default_currency','USD') }}</div>
    </section>
    <section class="card">
        @if($ledger instanceof \Illuminate\Pagination\LengthAwarePaginator && $ledger->count())
            <table>
                <thead><tr><th>Date</th><th>Type</th><th>Reason</th><th>Description</th><th>Amount</th><th>Balance</th></tr></thead>
                <tbody>
                @foreach($ledger as $entry)
                    <tr>
                        <td>{{ $entry->created_at }}</td>
                        <td class="{{ $entry->entry_type }}">{{ ucfirst($entry->entry_type) }}</td>
                        <td>{{ str_replace('_',' ',ucfirst($entry->reason)) }}</td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->entry_type === 'credit' ? '+' : '-' }}{{ number_format((float)$entry->amount,2) }}</td>
                        <td>{{ number_format((float)$entry->balance_after,2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No wallet transactions yet.</div>
        @endif
    </section>



@endsection