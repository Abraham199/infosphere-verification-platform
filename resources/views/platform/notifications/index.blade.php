@extends('layouts.platform')

@section('title', 'Notification Center')

@section('content')
@include('platform.notifications.partials.nav')

<div class="ivp-grid-4 mb-3">
    <x-ui.stat-card label="Queued" value="{{ $stats['queued'] }}" icon="fa-clock" tone="warning" />
    <x-ui.stat-card label="Sent" value="{{ $stats['sent'] }}" icon="fa-circle-check" tone="success" />
    <x-ui.stat-card label="Failed" value="{{ $stats['failed'] }}" icon="fa-triangle-exclamation" tone="warning" />
    <x-ui.stat-card label="Retrying" value="{{ $stats['retrying'] }}" icon="fa-rotate" tone="info" />
</div>

<x-ui.card title="Delivery Status" subtitle="Recent notification deliveries across channels.">
    @include('platform.notifications.partials.filters', ['deliveryStatuses' => $deliveryStatuses])
    <x-ui.table
        :headers="['Reference', 'Type', 'Channel', 'Recipient', 'Status', 'Attempts']"
        :rows="$deliveries->getCollection()->map(fn ($delivery) => [
            '<a href=&quot;'.e(route('platform.notifications.delivery', ['delivery' => $delivery->id])).'&quot;>'.e($delivery->delivery_reference).'</a>',
            e($delivery->notification?->event_type ?? '-'),
            e(str($delivery->channel->value)->headline()),
            e($delivery->recipient),
            view('components.ui.status-badge', ['status' => $delivery->status->value])->render(),
            e((string) $delivery->attempts),
        ])->all()"
        empty="No notification deliveries yet."
    />
</x-ui.card>

<x-ui.card class="mt-3" title="Notification Dashboard" subtitle="Recent queued notification records.">
    <x-ui.table
        :headers="['Subject', 'Tenant', 'Type', 'Status', 'Queued']"
        :rows="$notifications->getCollection()->map(fn ($notification) => [
            '<a href=&quot;'.e(route('platform.notifications.show', ['notification' => $notification->id])).'&quot;>'.e($notification->subject ?? $notification->title ?? $notification->event_type).'</a>',
            e($notification->tenant?->name ?? 'Platform'),
            e($notification->event_type),
            view('components.ui.status-badge', ['status' => $notification->status->value])->render(),
            e($notification->queued_at?->format('M j, Y H:i') ?? '-'),
        ])->all()"
        empty="No notifications yet."
    />
</x-ui.card>
@endsection
