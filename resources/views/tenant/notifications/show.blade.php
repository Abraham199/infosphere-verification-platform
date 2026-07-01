@extends('layouts.tenant')

@section('title', 'Notification Detail')

@section('content')
@include('tenant.notifications.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

<x-ui.card title="{{ $notification->subject ?? $notification->title ?? 'Notification' }}" subtitle="{{ $notification->event_type }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Status" value="{{ str($notification->status->value)->headline() }}" icon="fa-circle-info" tone="primary" />
        <x-ui.stat-card label="Read" value="{{ $notification->read_at ? $notification->read_at->format('M j, Y H:i') : 'Unread' }}" icon="fa-envelope-open" tone="{{ $notification->read_at ? 'success' : 'warning' }}" />
        <x-ui.stat-card label="Priority" value="{{ str($notification->priority)->headline() }}" icon="fa-flag" tone="info" />
    </div>
    <p class="mt-3 mb-0">{{ $notification->body }}</p>
    <div class="d-flex flex-wrap gap-2 mt-3">
        @if($notification->read_at === null)
            <form method="POST" action="{{ route('tenant.notifications.read.update', ['tenant' => $tenant, 'notification' => $notification->id]) }}">
                @csrf
                @method('PATCH')
                <x-ui.button type="submit" variant="primary" icon="fa-envelope-open">Mark as Read</x-ui.button>
            </form>
        @endif
        <form method="POST" action="{{ route('tenant.notifications.archive', ['tenant' => $tenant, 'notification' => $notification->id]) }}">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="outline-secondary" icon="fa-box-archive">Archive</x-ui.button>
        </form>
    </div>
</x-ui.card>

<x-ui.card class="mt-3" title="Delivery Channels" subtitle="Delivery records for this notification.">
    <x-ui.table
        :headers="['Channel', 'Recipient', 'Status', 'Sent', 'Failure']"
        :rows="$notification->deliveries->map(fn ($delivery) => [
            e(str($delivery->channel->value)->headline()),
            e($delivery->recipient),
            view('components.ui.status-badge', ['status' => $delivery->status->value])->render(),
            e($delivery->sent_at?->format('M j, Y H:i') ?? '-'),
            e($delivery->failure_reason ?? '-'),
        ])->all()"
        empty="No delivery records for this notification."
    />
</x-ui.card>
@endsection
