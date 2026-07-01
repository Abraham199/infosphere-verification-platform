@extends('layouts.platform')

@section('title', 'Support Ticket')

@section('content')
@php($nextStatusOptions = ['' => 'Select next status'] + collect($statuses)->mapWithKeys(fn ($case) => [$case->value => str($case->value)->headline()->toString()])->all())
@php($assignmentOptions = collect($assignmentTypes)->mapWithKeys(fn ($case) => [$case->value => str($case->value)->headline()->toString()])->all())

@if($errors->any())
    <x-ui.alert variant="danger" icon="fa-triangle-exclamation">{{ $errors->first() }}</x-ui.alert>
@endif

<x-ui.card title="{{ $ticket->subject }}" subtitle="Reference {{ $ticket->ticket_reference }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Tenant" value="{{ $ticket->tenant?->name ?? 'Platform' }}" icon="fa-building" tone="primary" />
        <x-ui.stat-card label="Status" value="{{ str($ticket->status->value)->headline() }}" icon="fa-circle-info" tone="info" />
        <x-ui.stat-card label="SLA" value="{{ $isBreached ? 'Breached' : 'Tracked' }}" icon="fa-stopwatch" tone="{{ $isBreached ? 'warning' : 'success' }}" />
    </div>
    <div class="mt-3">
        <p class="ivp-muted mb-1">Description</p>
        <p class="mb-0">{{ $ticket->description }}</p>
    </div>
</x-ui.card>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Status" subtitle="Advance the ticket through the Support Engine lifecycle.">
        <form class="vstack gap-3" method="POST" action="{{ route('platform.support.status', ['reference' => $ticket->ticket_reference]) }}">
            @csrf
            @method('PATCH')
            <x-ui.select label="Next status" name="status" :options="$nextStatusOptions" />
            <x-ui.button type="submit" variant="primary" icon="fa-arrow-right">Update Status</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card title="Assignment" subtitle="Assign to a team or named support user.">
        <form class="vstack gap-3" method="POST" action="{{ route('platform.support.assignments.store', ['reference' => $ticket->ticket_reference]) }}">
            @csrf
            <x-ui.select label="Assignment type" name="assignment_type" :options="$assignmentOptions" :selected="old('assignment_type', 'team')" />
            <x-ui.input label="Assigned team" name="assigned_team" :value="old('assigned_team', 'support-tier-1')" />
            <x-ui.input label="Assigned user ID" name="assigned_to_user_id" :value="old('assigned_to_user_id')" />
            <x-ui.button type="submit" variant="primary" icon="fa-user-check">Assign Ticket</x-ui.button>
        </form>
    </x-ui.card>
</div>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Notes" subtitle="Public replies and internal operational notes.">
        @forelse($ticket->notes as $note)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between gap-2">
                    <p class="fw-semibold mb-1">{{ $note->is_internal ? 'Internal note' : 'Public reply' }}</p>
                    <x-ui.status-badge :status="$note->is_internal ? 'internal' : 'public'" />
                </div>
                <p class="mb-1">{{ $note->body }}</p>
                <p class="ivp-muted mb-0">{{ $note->author?->name ?? 'Support' }} - {{ $note->created_at?->format('M j, Y H:i') }}</p>
            </div>
        @empty
            <x-ui.empty-state compact title="No notes yet" icon="fa-comments" />
        @endforelse
    </x-ui.card>

    <x-ui.card title="Internal Note" subtitle="Add staff-only context for support operators.">
        <form class="vstack gap-3" method="POST" action="{{ route('platform.support.internal-notes.store', ['reference' => $ticket->ticket_reference]) }}">
            @csrf
            <div>
                <label class="form-label" for="body">Note</label>
                <textarea id="body" name="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <x-ui.button type="submit" variant="primary" icon="fa-note-sticky">Add Internal Note</x-ui.button>
        </form>
    </x-ui.card>
</div>

<x-ui.card class="mt-3" title="Assignments" subtitle="Assignment history.">
    <x-ui.table
        :headers="['Type', 'Team', 'User', 'Assigned By', 'Assigned At']"
        :rows="$ticket->assignments->sortByDesc('assigned_at')->map(fn ($assignment) => [
            e(str($assignment->assignment_type->value)->headline()),
            e($assignment->assigned_team ?? '-'),
            e($assignment->assignee?->name ?? $assignment->assigned_to_user_id ?? '-'),
            e($assignment->assigned_by ?? '-'),
            e($assignment->assigned_at?->format('M j, Y H:i') ?? '-'),
        ])->values()->all()"
        empty="No assignments recorded."
    />
</x-ui.card>
@endsection
