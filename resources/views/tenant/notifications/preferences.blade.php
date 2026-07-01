@extends('layouts.tenant')

@section('title', 'Notification Preferences')

@section('content')
@include('tenant.notifications.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

@php($rows = $preferences->groupBy('notification_type'))

<x-ui.card title="Notification Preferences" subtitle="Manage optional tenant-user notification channels.">
    <form method="POST" action="{{ route('tenant.notifications.preferences.update', ['tenant' => $tenant]) }}">
        @csrf
        @method('PUT')
        <x-ui.table
            :headers="array_merge(['Notification Type'], collect($channels)->map(fn ($channel) => str($channel->value)->headline()->toString())->all())"
            :rows="collect($types)->map(function ($definition, $eventType) use ($channels, $rows) {
                $cells = [e($definition['label'])];
                foreach ($channels as $channel) {
                    $preference = ($rows[$eventType] ?? collect())->first(fn ($row) => $row->channel->value === $channel->value);
                    $checked = $preference?->is_enabled ?? true;
                    $mandatory = $definition['category']->isMandatory();
                    $cells[] = '<input class=&quot;form-check-input&quot; type=&quot;checkbox&quot; name=&quot;preferences['.e($eventType).']['.e($channel->value).']&quot; value=&quot;1&quot; '.($checked ? 'checked' : '').' '.($mandatory ? 'disabled' : '').'>'.($mandatory ? '<span class=&quot;ms-2 text-muted&quot;>Required</span>' : '');
                }
                return $cells;
            })->values()->all()"
            empty="No notification types configured."
        />
        <div class="mt-3">
            <x-ui.button type="submit" variant="primary" icon="fa-floppy-disk">Save Preferences</x-ui.button>
        </div>
    </form>
</x-ui.card>
@endsection
