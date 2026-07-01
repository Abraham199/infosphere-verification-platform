@extends('layouts.tenant')

@section('title', 'Support Ticket')

@section('content')
@include('tenant.support.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

@if($errors->any())
    <x-ui.alert variant="danger" icon="fa-triangle-exclamation">{{ $errors->first() }}</x-ui.alert>
@endif

<x-ui.card title="{{ $ticket->subject }}" subtitle="Reference {{ $ticket->ticket_reference }}">
    <div class="ivp-grid-3">
        <x-ui.stat-card label="Status" value="{{ str($ticket->status->value)->headline() }}" icon="fa-circle-info" tone="primary" />
        <x-ui.stat-card label="Priority" value="{{ $ticket->priority?->name ?? 'Normal' }}" icon="fa-flag" tone="warning" />
        <x-ui.stat-card label="SLA" value="{{ $isBreached ? 'Breached' : 'Tracked' }}" icon="fa-stopwatch" tone="{{ $isBreached ? 'warning' : 'success' }}" />
    </div>
    <div class="mt-3">
        <p class="ivp-muted mb-1">Description</p>
        <p class="mb-0">{{ $ticket->description }}</p>
    </div>
</x-ui.card>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Conversation" subtitle="Public notes visible to tenant users.">
        @forelse($ticket->notes->where('is_internal', false) as $note)
            <div class="border-bottom pb-3 mb-3">
                <p class="mb-1">{{ $note->body }}</p>
                <p class="ivp-muted mb-0">{{ $note->author?->name ?? 'Support' }} - {{ $note->created_at?->format('M j, Y H:i') }}</p>
            </div>
        @empty
            <x-ui.empty-state compact title="No replies yet" icon="fa-comments" />
        @endforelse
    </x-ui.card>

    <x-ui.card title="Add Reply" subtitle="Continue the support conversation.">
        <form class="vstack gap-3" method="POST" action="{{ route('tenant.support.notes.store', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference]) }}">
            @csrf
            <div>
                <label class="form-label" for="body">Reply</label>
                <textarea id="body" name="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <x-ui.button type="submit" variant="primary" icon="fa-reply">Add Reply</x-ui.button>
        </form>
    </x-ui.card>
</div>
@endsection
