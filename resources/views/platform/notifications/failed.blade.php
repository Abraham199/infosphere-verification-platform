@extends('layouts.platform')

@section('title', 'Failed Notifications')

@section('content')
@include('platform.notifications.partials.nav')

<x-ui.card title="Failed Notifications" subtitle="Failed delivery attempts with retry controls.">
    @include('platform.notifications.partials.filters')
    <x-ui.table
        :headers="['Reference', 'Type', 'Channel', 'Recipient', 'Failure', 'Action']"
        :rows="$deliveries->getCollection()->map(fn ($delivery) => [
            '<a href=&quot;'.e(route('platform.notifications.delivery', ['delivery' => $delivery->id])).'&quot;>'.e($delivery->delivery_reference).'</a>',
            e($delivery->notification?->event_type ?? '-'),
            e(str($delivery->channel->value)->headline()),
            e($delivery->recipient),
            e(str($delivery->failure_reason ?? 'No failure reason recorded.')->limit(80)),
            '<form method=&quot;POST&quot; action=&quot;'.e(route('platform.notifications.delivery.retry', ['delivery' => $delivery->id])).'&quot;><input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;'.e(csrf_token()).'&quot;><button class=&quot;btn btn-sm btn-primary&quot; type=&quot;submit&quot;><i class=&quot;fa-solid fa-rotate me-2&quot;></i>Retry</button></form>',
        ])->all()"
        empty="No failed notification deliveries."
    />
    <div class="mt-3">{{ $deliveries->links() }}</div>
</x-ui.card>
@endsection
