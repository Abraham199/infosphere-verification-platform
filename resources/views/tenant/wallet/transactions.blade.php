@extends('layouts.tenant')

@section('title', 'Wallet Transactions')

@section('content')
@include('tenant.wallet.partials.nav')

<x-ui.card title="Transaction History" subtitle="Latest wallet credits, debits, reservations, releases, refunds, and adjustments.">
    <x-ui.table
        :headers="['Reference', 'Type', 'Amount', 'Balance After', 'Status', 'Created']"
        :rows="collect($transactions)->map(fn ($row) => [
            e($row['reference'] ?? '-'),
            e(str($row['type'] ?? '-')->headline()),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['balance_after'] ?? 0), 2)),
            view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            e((string) ($row['created_at'] ?? '-')),
        ])->all()"
        empty="No transactions have been recorded for this wallet."
    />
</x-ui.card>
@endsection
