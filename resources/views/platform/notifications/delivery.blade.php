@extends('layouts.platform')

@section('title', 'Delivery Detail')

@section('content')
@include('platform.notifications.partials.nav')

<x-ui.card title="{{ $delivery->delivery_reference }}" subtitle="{{ $delivery->notification?->event_type ?? 'Notification delivery' }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Channel" value="{{ str($delivery->channel->value)->headline() }}" icon="fa-paper-plane" tone="primary" />
        <x-ui.stat-card label="Status" value="{{ str($delivery->status->value)->headline() }}" icon="fa-circle-info" tone="info" />
        <x-ui.stat-card label="Attempts" value="{{ $delivery->attempts }} / {{ $delivery->max_attempts }}" icon="fa-rotate" tone="warning" />
    </div>
    @if($delivery->failure_reason)
        <x-ui.alert class="mt-3" variant="danger" icon="fa-triangle-exclamation">{{ $delivery->failure_reason }}</x-ui.alert>
    @endif
    <div class="d-flex flex-wrap gap-2 mt-3">
        <x-ui.link-button :href="route('platform.notifications.show', ['notification' => $delivery->notification_id])" variant="outline-secondary" icon="fa-arrow-left">Notification</x-ui.link-button>
        @if($delivery->status->value === 'failed')
            <form method="POST" action="{{ route('platform.notifications.delivery.retry', ['delivery' => $delivery->id]) }}">
                @csrf
                <x-ui.button type="submit" variant="primary" icon="fa-rotate">Retry Delivery</x-ui.button>
            </form>
        @endif
    </div>
</x-ui.card>

<x-ui.card class="mt-3" title="Channel Logs" subtitle="Provider request and response records.">
    <x-ui.table
        :headers="['Status', 'Direction', 'Duration', 'Error', 'Created']"
        :rows="$delivery->logs->map(fn ($log) => [
            e($log->status),
            e(str($log->direction)->headline()),
            e(($log->duration_ms ?? 0).' ms'),
            e($log->error_message ?? '-'),
            e($log->created_at?->format('M j, Y H:i') ?? '-'),
        ])->all()"
        empty="No channel logs recorded."
    />
</x-ui.card>
@endsection
