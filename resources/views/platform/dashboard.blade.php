@extends('layouts.platform')

@section('title', 'Platform Overview')

@section('content')
<x-ui.breadcrumbs :items="['Platform' => route('platform.dashboard'), 'Overview' => null]" />

<x-dashboard.metric-grid :stats="$stats" />

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Reporting Cards" subtitle="Read-model summaries prepared for dashboards.">
        <div class="ivp-grid-2">
            @foreach($sections as $section)
                <x-ui.stat-card :label="$section['title']" :value="$section['metric']" :icon="$section['icon']" tone="primary" :caption="$section['caption']" />
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.card title="Recent Activity" subtitle="High-level operational signals.">
        <div class="list-group list-group-flush">
            @foreach($activity as $item)
                <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                    <span class="ivp-muted">{{ $item['label'] }}</span>
                    <strong class="text-end">{{ $item['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </x-ui.card>
</div>

<x-ui.card class="mt-3" title="Quick Actions" subtitle="Common administration entry points.">
    <div class="d-flex flex-wrap gap-2">
        <x-ui.link-button :href="route('platform.wallet.index')" variant="outline-primary" icon="fa-wallet">Review Wallets</x-ui.link-button>
        <x-ui.link-button :href="route('platform.verification.index')" variant="outline-primary" icon="fa-id-card">Monitor Verifications</x-ui.link-button>
        <x-ui.link-button :href="route('platform.products.index')" variant="outline-primary" icon="fa-boxes-stacked">Manage Products</x-ui.link-button>
    </div>
</x-ui.card>
@endsection
