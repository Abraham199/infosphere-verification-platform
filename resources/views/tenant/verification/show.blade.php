@extends('layouts.tenant')

@section('title', 'Verification Status')

@section('content')
@include('tenant.verification.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

@php($status = $verification->status?->value ?? (string) $verification->status)

<x-ui.card title="Verification Status" subtitle="Reference {{ $verification->reference }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Service" value="{{ $verification->service?->name ?? 'Verification' }}" icon="fa-id-card" tone="primary" />
        <x-ui.stat-card label="Amount" value="{{ $verification->currency }} {{ number_format((float) $verification->price_charged, 2) }}" icon="fa-tag" tone="success" />
        <x-ui.stat-card label="Status" value="{{ str($status)->headline() }}" icon="fa-circle-info" tone="{{ $status === 'failed' ? 'warning' : 'info' }}" />
    </div>

    <div class="mt-3">
        @if($status === 'completed')
            <x-ui.alert variant="success" icon="fa-circle-check">Verification completed successfully.</x-ui.alert>
        @elseif($status === 'failed')
            <x-ui.alert variant="danger" icon="fa-circle-xmark">{{ $verification->failure_reason ?: 'Verification failed. Any active reservation is released by the Verification Platform.' }}</x-ui.alert>
        @else
            <x-ui.alert variant="info" icon="fa-clock">Verification is currently {{ str($status)->headline() }}.</x-ui.alert>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <x-ui.link-button :href="route('tenant.verification.result', ['tenant' => $tenant, 'reference' => $verification->reference])" variant="primary" icon="fa-file-lines">View Result</x-ui.link-button>
        <x-ui.link-button :href="route('tenant.verification.history', ['tenant' => $tenant])" variant="outline-secondary" icon="fa-arrow-left">Back to History</x-ui.link-button>
    </div>
</x-ui.card>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Wallet Reservation" subtitle="Reservation lifecycle information.">
        @if($verification->reservation)
            <x-ui.table
                :headers="['Reference', 'Amount', 'Captured', 'Released', 'Status']"
                :rows="[[
                    e($verification->reservation->reference),
                    e($verification->reservation->currency.' '.number_format((float) $verification->reservation->amount, 2)),
                    e($verification->reservation->currency.' '.number_format((float) $verification->reservation->captured_amount, 2)),
                    e($verification->reservation->currency.' '.number_format((float) $verification->reservation->released_amount, 2)),
                    view('components.ui.status-badge', ['status' => $verification->reservation->status?->value ?? $verification->reservation->status])->render(),
                ]]"
            />
        @else
            <x-ui.empty-state compact title="No reservation linked" icon="fa-lock" />
        @endif
    </x-ui.card>

    <x-ui.card title="Wallet Transaction" subtitle="Debit transaction after successful verification.">
        @if($verification->walletTransaction)
            <x-ui.table
                :headers="['Reference', 'Type', 'Amount', 'Status']"
                :rows="[[
                    e($verification->walletTransaction->reference),
                    e(str($verification->walletTransaction->type?->value ?? $verification->walletTransaction->type)->headline()),
                    e($verification->walletTransaction->currency.' '.number_format((float) $verification->walletTransaction->amount, 2)),
                    view('components.ui.status-badge', ['status' => $verification->walletTransaction->status?->value ?? $verification->walletTransaction->status])->render(),
                ]]"
            />
        @else
            <x-ui.empty-state compact title="No wallet debit yet" icon="fa-receipt" />
        @endif
    </x-ui.card>
</div>
@endsection
