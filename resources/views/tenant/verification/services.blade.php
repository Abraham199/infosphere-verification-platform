@extends('layouts.tenant')

@section('title', 'Verification Services')

@section('content')
@include('tenant.verification.partials.nav')

<x-ui.card title="Available Services" subtitle="Tenant pricing resolved through the Verification Pricing Engine.">
    <x-ui.table
        :headers="['Service', 'Code', 'Price', 'Status', 'Action']"
        :rows="collect($services)->map(fn ($service) => [
            e($service['name']),
            e($service['service_code']),
            e($service['currency'].' '.number_format((float) $service['price'], 2)),
            view('components.ui.status-badge', ['status' => $service['status']])->render(),
            '<a class=&quot;btn btn-sm btn-outline-primary&quot; href=&quot;'.e(route('tenant.verification.index', ['tenant' => $tenant, 'service' => $service['service_code']])).'&quot;><i class=&quot;fa-solid fa-arrow-right me-1&quot;></i>Select</a>',
        ])->all()"
        empty="No active verification services are configured."
    />
</x-ui.card>
@endsection
