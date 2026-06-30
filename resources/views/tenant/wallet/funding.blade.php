@extends('layouts.tenant')

@section('title', 'Fund Wallet')

@section('content')
@include('tenant.wallet.partials.nav')

@if($errors->any())
    <x-ui.alert variant="danger" icon="fa-triangle-exclamation">
        {{ $errors->first() }}
    </x-ui.alert>
@endif

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

<div class="ivp-grid-2">
    <x-ui.card title="Wallet Funding Form" subtitle="Initialize a funding attempt through the Payment Platform.">
        @if($wallet === null)
            <x-ui.empty-state title="No fundable wallet" message="An NGN wallet must exist before funding can begin." icon="fa-wallet" />
        @else
            <form method="POST" action="{{ route('tenant.wallet.funding.store', ['tenant' => $tenant]) }}" class="vstack gap-3">
                @csrf
                <x-ui.input label="Amount" name="amount" type="number" value="{{ old('amount') }}" min="100" step="100" placeholder="5000" required />
                <x-ui.select label="Currency" name="currency" :options="['NGN' => 'NGN']" selected="{{ old('currency', 'NGN') }}" />
                <x-ui.input label="Payment email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" required />
                <x-ui.alert variant="info" icon="fa-circle-info">After initialization, continue using the provider authorization link when live Paystack credentials are configured.</x-ui.alert>
                <x-ui.button type="submit" variant="primary" icon="fa-credit-card">Initialize Payment</x-ui.button>
            </form>
        @endif
    </x-ui.card>

    <x-ui.card title="Funding History" subtitle="Recent payment initialization records.">
        <x-ui.table
            :headers="['Reference', 'Amount', 'Status', 'Created']"
            :rows="collect($funding)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()"
            empty="No funding attempts yet."
        />
    </x-ui.card>
</div>
@endsection
