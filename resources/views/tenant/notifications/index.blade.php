@extends('layouts.tenant')

@section('title', 'Notification Inbox')

@section('content')
@include('tenant.notifications.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

<x-ui.card title="{{ $scope ? str($scope)->headline().' Notifications' : 'Inbox' }}" subtitle="Tenant-scoped in-app notification records.">
    <form class="row g-2 mb-3" method="GET">
        <div class="col-md-6">
            <label class="form-label" for="event_type">Type</label>
            <select id="event_type" name="event_type" class="form-select">
                <option value="">All types</option>
                @foreach($types as $key => $definition)
                    <option value="{{ $key }}" @selected(($filters['event_type'] ?? null) === $key)>{{ $definition['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <x-ui.button type="submit" variant="primary" icon="fa-filter">Filter</x-ui.button>
        </div>
    </form>

    <x-ui.table
        :headers="['Subject', 'Type', 'Status', 'Read', 'Queued']"
        :rows="$notifications->getCollection()->map(fn ($notification) => [
            '<a href=&quot;'.e(route('tenant.notifications.show', ['tenant' => $tenant, 'notification' => $notification->id])).'&quot;>'.e($notification->subject ?? $notification->title ?? $notification->event_type).'</a>',
            e($types[$notification->event_type]['label'] ?? $notification->event_type),
            view('components.ui.status-badge', ['status' => $notification->status->value])->render(),
            e($notification->read_at ? 'Yes' : 'No'),
            e($notification->queued_at?->format('M j, Y H:i') ?? '-'),
        ])->all()"
        empty="No notifications found."
    />
    <div class="mt-3">{{ $notifications->links() }}</div>
</x-ui.card>
@endsection
