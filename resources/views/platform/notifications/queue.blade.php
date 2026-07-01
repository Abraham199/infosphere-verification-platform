@extends('layouts.platform')

@section('title', 'Notification Queue')

@section('content')
@include('platform.notifications.partials.nav')

<x-ui.card title="Notification Queue" subtitle="Filter delivery records by type, channel, and status.">
    @include('platform.notifications.partials.filters', ['deliveryStatuses' => $deliveryStatuses])
    <x-ui.table
        :headers="['Reference', 'Tenant', 'Type', 'Channel', 'Status', 'Next Retry', 'Created']"
        :rows="$deliveries->getCollection()->map(fn ($delivery) => [
            '<a href=&quot;'.e(route('platform.notifications.delivery', ['delivery' => $delivery->id])).'&quot;>'.e($delivery->delivery_reference).'</a>',
            e($delivery->notification?->tenant?->name ?? 'Platform'),
            e($delivery->notification?->event_type ?? '-'),
            e(str($delivery->channel->value)->headline()),
            view('components.ui.status-badge', ['status' => $delivery->status->value])->render(),
            e($delivery->next_retry_at?->format('M j, Y H:i') ?? '-'),
            e($delivery->created_at?->format('M j, Y H:i') ?? '-'),
        ])->all()"
        empty="No queued deliveries found."
    />
    <div class="mt-3">{{ $deliveries->links() }}</div>
</x-ui.card>
@endsection
