@extends('layouts.tenant')

@section('title', 'Payment Status')

@section('content')
@include('tenant.wallet.partials.nav')

@php($status = $payment->status?->value ?? (string) $payment->status)

<x-ui.card title="Payment Initialization Status" subtitle="Reference {{ $payment->reference }}">
    <div class="ivp-grid-2">
        <x-ui.stat-card label="Amount" value="{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}" icon="fa-money-bill" tone="primary" />
        <x-ui.stat-card label="Status" value="{{ str($status)->headline() }}" icon="fa-circle-info" tone="{{ $status === 'failed' ? 'warning' : 'success' }}" />
    </div>

    <div class="mt-3">
        @if($status === 'pending')
            <x-ui.alert variant="warning" icon="fa-clock">Payment is pending. Complete payment through the provider authorization link if available.</x-ui.alert>
        @elseif($status === 'failed')
            <x-ui.alert variant="danger" icon="fa-circle-xmark">{{ $payment->failure_reason ?: 'Payment initialization failed.' }}</x-ui.alert>
        @elseif($status === 'success')
            <x-ui.alert variant="success" icon="fa-circle-check">Payment succeeded and wallet credit processing has completed.</x-ui.alert>
        @else
            <x-ui.alert variant="info" icon="fa-circle-info">Payment has been initialized.</x-ui.alert>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        @if($payment->authorization_url)
            <x-ui.link-button :href="$payment->authorization_url" variant="primary" icon="fa-up-right-from-square">Continue to Provider</x-ui.link-button>
        @endif
        <x-ui.link-button :href="route('tenant.wallet.funding.index', ['tenant' => $tenant])" variant="outline-secondary" icon="fa-arrow-left">Back to Funding</x-ui.link-button>
    </div>
</x-ui.card>
@endsection
