@extends('layouts.tenant')

@section('title', 'Customer Dashboard')

@section('content')
<x-ui.card title="Customer Portal Foundation" subtitle="Base customer workspace prepared for future workflows.">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Verification Requests" value="Ready" icon="fa-id-card" tone="info" caption="Customer request flow placeholder" />
        <x-ui.stat-card label="Wallet Access" value="Scoped" icon="fa-wallet" tone="primary" caption="Tenant-controlled visibility" />
        <x-ui.stat-card label="Support" value="Prepared" icon="fa-life-ring" tone="success" caption="Support handoff ready" />
    </div>
</x-ui.card>
@endsection
