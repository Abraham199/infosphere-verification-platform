@extends('layouts.tenant')

@section('title', 'Wallet Overview')

@section('content')
@include('tenant.wallet.partials.nav')

@if($wallets === [])
    <x-ui.card>
        <x-ui.empty-state title="No wallet found" message="This tenant does not have an NGN wallet yet. Wallet creation remains a backend operational action." icon="fa-wallet" />
    </x-ui.card>
@else
    <div class="ivp-grid-2">
        @foreach($wallets as $wallet)
            <x-ui.card title="{{ $wallet['currency'] ?? 'NGN' }} Wallet" subtitle="Current tenant wallet balance state.">
                <div class="ivp-grid-2">
                    <x-ui.stat-card label="Available" value="{{ number_format((float) ($wallet['available_balance'] ?? 0), 2) }}" icon="fa-circle-check" tone="success" />
                    <x-ui.stat-card label="Reserved" value="{{ number_format((float) ($wallet['reserved_balance'] ?? 0), 2) }}" icon="fa-lock" tone="warning" />
                    <x-ui.stat-card label="Frozen" value="{{ number_format((float) ($wallet['frozen_balance'] ?? 0), 2) }}" icon="fa-snowflake" tone="info" />
                    <x-ui.stat-card label="Status" value="{{ str($wallet['status'] ?? 'active')->headline() }}" icon="fa-shield" tone="primary" />
                </div>
            </x-ui.card>
        @endforeach
        <x-ui.card title="Funding" subtitle="Start a funding attempt through the Payment Platform.">
            <p class="ivp-muted">Payment initialization is handled by the provider-agnostic Payment Platform. Wallet never talks to Paystack directly.</p>
            <x-ui.link-button :href="route('tenant.wallet.funding.index', ['tenant' => $tenant])" variant="primary" icon="fa-credit-card">Fund Wallet</x-ui.link-button>
        </x-ui.card>
    </div>
@endif
@endsection
