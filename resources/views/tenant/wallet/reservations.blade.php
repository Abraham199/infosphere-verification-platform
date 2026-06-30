@extends('layouts.tenant')

@section('title', 'Wallet Reservations')

@section('content')
@include('tenant.wallet.partials.nav')

<x-ui.card title="Reservation History" subtitle="Funds reserved for verification or service workflows.">
    <x-ui.table
        :headers="['Reference', 'Amount', 'Captured', 'Released', 'Status', 'Created']"
        :rows="collect($reservations)->map(fn ($row) => [
            e($row['reference'] ?? '-'),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['captured_amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['released_amount'] ?? 0), 2)),
            view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            e((string) ($row['created_at'] ?? '-')),
        ])->all()"
        empty="No reservations have been recorded for this wallet."
    />
</x-ui.card>
@endsection
