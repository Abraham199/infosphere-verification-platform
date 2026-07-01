@extends('layouts.platform')

@section('title', 'Notification Detail')

@section('content')
@include('platform.notifications.partials.nav')

<x-ui.card title="{{ $notification->subject ?? $notification->title ?? 'Notification' }}" subtitle="{{ $notification->event_type }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Tenant" value="{{ $notification->tenant?->name ?? 'Platform' }}" icon="fa-building" tone="primary" />
        <x-ui.stat-card label="Status" value="{{ str($notification->status->value)->headline() }}" icon="fa-circle-info" tone="info" />
        <x-ui.stat-card label="Priority" value="{{ str($notification->priority)->headline() }}" icon="fa-flag" tone="warning" />
    </div>
    <p class="mt-3 mb-0">{{ $notification->body }}</p>
</x-ui.card>

<x-ui.card class="mt-3" title="Deliveries" subtitle="Channel delivery state for this notification.">
    <x-ui.table
        :headers="['Reference', 'Channel', 'Recipient', 'Status', 'Attempts', 'Failure']"
        :rows="$notification->deliveries->map(fn ($delivery) => [
            '<a href=&quot;'.e(route('platform.notifications.delivery', ['delivery' => $delivery->id])).'&quot;>'.e($delivery->delivery_reference).'</a>',
            e(str($delivery->channel->value)->headline()),
            e($delivery->recipient),
            view('components.ui.status-badge', ['status' => $delivery->status->value])->render(),
            e((string) $delivery->attempts),
            e($delivery->failure_reason ?? '-'),
        ])->all()"
        empty="No deliveries recorded."
    />
</x-ui.card>
@endsection
