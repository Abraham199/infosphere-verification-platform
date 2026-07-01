@extends('layouts.tenant')

@section('title', 'Support Desk')

@section('content')
@include('tenant.support.partials.nav')

@if(session('status'))
    <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
@endif

@if($errors->any())
    <x-ui.alert variant="danger" icon="fa-triangle-exclamation">{{ $errors->first() }}</x-ui.alert>
@endif

<div class="ivp-grid-2">
    <x-ui.card title="Open Support Ticket" subtitle="Create a tenant-scoped case for the enterprise support queue.">
        <form class="vstack gap-3" method="POST" action="{{ route('tenant.support.store', ['tenant' => $tenant]) }}">
            @csrf
            <x-ui.input label="Subject" name="subject" :value="old('subject')" placeholder="Brief summary" required />
            <x-ui.select
                label="Category"
                name="support_category_id"
                :options="['' => 'General support'] + $categories->pluck('name', 'id')->all()"
                :selected="old('support_category_id')"
            />
            <x-ui.select
                label="Priority"
                name="support_priority_id"
                :options="['' => 'Normal priority'] + $priorities->pluck('name', 'id')->all()"
                :selected="old('support_priority_id')"
            />
            <div>
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <x-ui.button type="submit" variant="primary" icon="fa-paper-plane">Submit Ticket</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card title="Knowledge Base" subtitle="Published support guidance for this tenant.">
        @forelse($articles as $article)
            <div class="border-bottom pb-3 mb-3">
                <p class="fw-semibold mb-1">{{ $article->title }}</p>
                <p class="ivp-muted mb-0">{{ str($article->body)->stripTags()->limit(180) }}</p>
            </div>
        @empty
            <x-ui.empty-state compact title="No published articles" message="Support articles will appear here after publication." icon="fa-book-open" />
        @endforelse
    </x-ui.card>
</div>

<x-ui.card class="mt-3" title="Support Tickets" subtitle="Your tenant support activity.">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <x-ui.link-button :href="route('tenant.support.index', ['tenant' => $tenant])" variant="{{ $status ? 'outline-secondary' : 'secondary' }}" size="sm">All</x-ui.link-button>
        @foreach(['open', 'assigned', 'in_progress', 'waiting_for_customer', 'resolved', 'closed'] as $filter)
            <x-ui.link-button :href="route('tenant.support.index', ['tenant' => $tenant, 'status' => $filter])" variant="{{ $status === $filter ? 'secondary' : 'outline-secondary' }}" size="sm">{{ str($filter)->headline() }}</x-ui.link-button>
        @endforeach
    </div>

    <x-ui.table
        :headers="['Reference', 'Subject', 'Priority', 'Status', 'Created']"
        :rows="$tickets->getCollection()->map(fn ($ticket) => [
            '<a href=&quot;'.e(route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference])).'&quot;>'.e($ticket->ticket_reference).'</a>',
            e($ticket->subject),
            e($ticket->priority?->name ?? 'Normal'),
            view('components.ui.status-badge', ['status' => $ticket->status->value])->render(),
            e($ticket->created_at?->format('M j, Y H:i') ?? '-'),
        ])->all()"
        empty="No support tickets yet."
    />

    <div class="mt-3">{{ $tickets->links() }}</div>
</x-ui.card>
@endsection
