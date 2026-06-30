@extends('layouts.platform')

@section('title', 'Wallet Operations')

@section('content')
<x-ui.tabs :tabs="[
    ['label' => 'Overview', 'href' => '#overview', 'icon' => 'fa-wallet', 'active' => true],
    ['label' => 'Transactions', 'href' => '#transactions', 'icon' => 'fa-list'],
    ['label' => 'Funding', 'href' => '#funding', 'icon' => 'fa-credit-card'],
    ['label' => 'Reservations', 'href' => '#reservations', 'icon' => 'fa-lock'],
]" />

<div class="ivp-grid-2">
    <x-ui.card id="overview" title="Wallet Overview" subtitle="Balances across tenant wallets.">
        <x-ui.table
            :headers="['Currency', 'Available', 'Reserved', 'Frozen', 'Status']"
            :rows="collect($wallets)->map(fn ($row) => [
                e($row['currency'] ?? 'NGN'),
                e(number_format((float) ($row['available_balance'] ?? 0), 2)),
                e(number_format((float) ($row['reserved_balance'] ?? 0), 2)),
                e(number_format((float) ($row['frozen_balance'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'active'])->render(),
            ])->all()"
            empty="No wallets found."
        />
    </x-ui.card>

    <x-ui.card id="funding" title="Funding History" subtitle="Recent payment funding records.">
        <x-ui.table
            :headers="['Reference', 'Amount', 'Status', 'Created']"
            :rows="collect($funding)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()"
            empty="No funding records yet."
        />
    </x-ui.card>
</div>

<div class="ivp-grid-2 mt-3">
    <x-ui.card id="transactions" title="Transaction History" subtitle="Latest wallet transactions.">
        <x-ui.table
            :headers="['Reference', 'Type', 'Amount', 'Status']"
            :rows="collect($transactions)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(str($row['type'] ?? '-')->headline()),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            ])->all()"
            empty="No wallet transactions yet."
        />
    </x-ui.card>

    <x-ui.card id="reservations" title="Reservation History" subtitle="Reserved funds waiting for capture or release.">
        <x-ui.table
            :headers="['Reference', 'Amount', 'Status', 'Created']"
            :rows="collect($reservations)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()"
            empty="No reservations yet."
        />
    </x-ui.card>
</div>
@endsection
