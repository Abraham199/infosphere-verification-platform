@extends('layouts.tenant')

@section('title', 'Verification Result')

@section('content')
@include('tenant.verification.partials.nav')

<x-ui.card title="Verification Result" subtitle="Reference {{ $verification->reference }}">
    @if($result)
        <div class="ivp-grid-3">
            <x-ui.stat-card label="Result" value="{{ str($result->result_status?->value ?? $result->result_status)->headline() }}" icon="fa-circle-check" tone="success" />
            <x-ui.stat-card label="Confidence" value="{{ $result->confidence_score ?? 'N/A' }}" icon="fa-gauge-high" tone="info" />
            <x-ui.stat-card label="Service" value="{{ $verification->service?->name ?? 'Verification' }}" icon="fa-id-card" tone="primary" />
        </div>
        <div class="mt-3">
            <x-ui.alert variant="info" icon="fa-file-lines">{{ $result->summary ?: 'No result summary was provided.' }}</x-ui.alert>
        </div>
        <x-ui.card class="mt-3" title="Normalized Data" subtitle="Provider-normalized result payload.">
            @if($result->normalized_data)
                <pre class="mb-0 small">{{ json_encode($result->normalized_data, JSON_PRETTY_PRINT) }}</pre>
            @else
                <x-ui.empty-state compact title="No normalized data" icon="fa-database" />
            @endif
        </x-ui.card>
    @else
        <x-ui.empty-state title="Result not available" message="The verification result will appear here once the request is completed." icon="fa-file-circle-question">
            <x-ui.link-button :href="route('tenant.verification.show', ['tenant' => $tenant, 'reference' => $verification->reference])" variant="outline-primary" icon="fa-arrow-left">Back to Status</x-ui.link-button>
        </x-ui.empty-state>
    @endif
</x-ui.card>
@endsection
