@extends('layouts.tenant')

@section('title', 'Tenant Dashboard')

@section('content')
<x-ui.breadcrumbs :items="['Workspace' => route('tenant.dashboard', ['tenant' => $tenant]), 'Dashboard' => null]" />

<x-dashboard.metric-grid :stats="$stats" />

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Recent Transactions" subtitle="Latest wallet movements for this tenant.">
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

    <x-ui.card title="Quick Verification" subtitle="Start with service selection and pricing review.">
        <form class="vstack gap-3" method="GET" action="{{ route('tenant.verification.index', ['tenant' => $tenant]) }}">
            <x-ui.select label="Verification service" name="service" :options="['nin_lookup' => 'NIN Lookup', 'bvn_lookup' => 'BVN Lookup', 'cac_lookup' => 'CAC Lookup']" />
            <x-ui.input label="Subject identifier" name="subject" placeholder="Enter customer identifier" />
            <x-ui.button type="submit" variant="primary" icon="fa-magnifying-glass">Continue</x-ui.button>
        </form>
    </x-ui.card>
</div>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Notifications" subtitle="Recent tenant messages.">
        <x-ui.table
            :headers="['Event', 'Status', 'Created']"
            :rows="collect($notifications)->map(fn ($row) => [
                e(str($row['event_type'] ?? '-')->headline()),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()"
            empty="No notifications yet."
        />
    </x-ui.card>

    <x-ui.card title="Support Tickets" subtitle="Recent help desk activity.">
        <x-ui.table
            :headers="['Reference', 'Subject', 'Status']"
            :rows="collect($support)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e($row['subject'] ?? '-'),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'open'])->render(),
            ])->all()"
            empty="No support tickets yet."
        />
    </x-ui.card>
</div>
@endsection
