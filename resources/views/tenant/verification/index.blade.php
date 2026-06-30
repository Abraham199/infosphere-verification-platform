@extends('layouts.tenant')

@section('title', 'Verification Request')

@section('content')
@include('tenant.verification.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

@if($errors->any())
    <x-ui.alert variant="danger" icon="fa-triangle-exclamation">{{ $errors->first() }}</x-ui.alert>
@endif

<div class="ivp-grid-2">
    <x-ui.card title="Submit Verification" subtitle="Select a service, confirm wallet readiness, and submit through the Verification Platform.">
        @if($services === [])
            <x-ui.empty-state title="No verification services" message="No active verification services are available for this tenant." icon="fa-id-card" />
        @else
            <form class="vstack gap-3" method="POST" action="{{ route('tenant.verification.request.store', ['tenant' => $tenant]) }}">
                @csrf
                <x-ui.select
                    label="Verification service"
                    name="service_code"
                    :options="collect($services)->pluck('name', 'service_code')->all()"
                    :selected="old('service_code', $selectedService['service_code'] ?? null)"
                    required
                />
                <x-ui.input label="Subject identifier" name="subject_identifier" :value="old('subject_identifier', request('subject'))" placeholder="NIN, BVN, CAC RC number, or supported identifier" required />
                <x-ui.input label="Customer name" name="customer_name" :value="old('customer_name')" placeholder="Optional display name" />
                <x-ui.input label="Customer phone" name="customer_phone" :value="old('customer_phone')" placeholder="Optional phone number" />
                <x-ui.input label="Customer email" name="customer_email" type="email" :value="old('customer_email')" placeholder="Optional email address" />
                <div>
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Optional internal note">{{ old('notes') }}</textarea>
                </div>
                <x-ui.alert variant="info" icon="fa-circle-info">Provider calls, wallet reservation, wallet debit, reservation release, and ledger posting are handled by the existing Verification Platform.</x-ui.alert>
                <x-ui.button type="submit" variant="primary" icon="fa-paper-plane">Submit Verification</x-ui.button>
            </form>
        @endif
    </x-ui.card>

    <div class="vstack gap-3">
        <x-ui.card title="Selected Pricing" subtitle="Pricing is resolved by the existing pricing engine.">
            @if($selectedService)
                <x-ui.stat-card label="{{ $selectedService['name'] }}" value="{{ $selectedService['currency'] }} {{ number_format((float) $selectedService['price'], 2) }}" icon="fa-tag" tone="success" caption="{{ $selectedService['service_code'] }}" />
            @else
                <x-ui.empty-state compact title="Select a service" message="Choose a verification service to preview pricing." icon="fa-tag" />
            @endif
        </x-ui.card>

        <x-ui.card title="Wallet Readiness" subtitle="Balance check before submission.">
            @if($wallet)
                <div class="ivp-grid-2">
                    <x-ui.stat-card label="Available" value="{{ $wallet->currency }} {{ number_format((float) $wallet->available_balance, 2) }}" icon="fa-wallet" tone="primary" />
                    <x-ui.stat-card label="Reserved" value="{{ $wallet->currency }} {{ number_format((float) $wallet->reserved_balance, 2) }}" icon="fa-lock" tone="warning" />
                </div>
            @else
                <x-ui.empty-state compact title="No wallet found" message="A tenant wallet is required before verification can be submitted." icon="fa-wallet" />
            @endif
        </x-ui.card>
    </div>
</div>

<x-ui.card class="mt-3" title="Recent Verification Requests" subtitle="Latest verification activity.">
    <x-ui.table
        :headers="['Reference', 'Provider', 'Amount', 'Status', 'Created']"
        :rows="collect($requests)->map(fn ($row) => [
            '<a href=&quot;'.e(route('tenant.verification.show', ['tenant' => $tenant, 'reference' => $row['reference'] ?? '-'])).'&quot;>'.e($row['reference'] ?? '-').'</a>',
            e($row['provider'] ?? '-'),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['price_charged'] ?? 0), 2)),
            view('components.ui.status-badge', ['status' => $row['status'] ?? 'created'])->render(),
            e((string) ($row['created_at'] ?? '-')),
        ])->all()"
        empty="No verification requests yet."
    />
</x-ui.card>
@endsection
