@extends('layouts.platform')

@section('title', 'Support Operations')

@section('content')
<x-ui.card title="Support Queue" subtitle="Enterprise ticket queue across tenants.">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <x-ui.link-button :href="route('platform.support.index')" variant="{{ $status ? 'outline-secondary' : 'secondary' }}" size="sm">All</x-ui.link-button>
        @foreach($statuses as $case)
            <x-ui.link-button :href="route('platform.support.index', ['status' => $case->value])" variant="{{ $status === $case->value ? 'secondary' : 'outline-secondary' }}" size="sm">{{ str($case->value)->headline() }}</x-ui.link-button>
        @endforeach
    </div>

    <x-ui.table
        :headers="['Reference', 'Tenant', 'Subject', 'Priority', 'Status', 'Created']"
        :rows="$tickets->getCollection()->map(fn ($ticket) => [
            '<a href=&quot;'.e(route('platform.support.show', ['reference' => $ticket->ticket_reference])).'&quot;>'.e($ticket->ticket_reference).'</a>',
            e($ticket->tenant?->name ?? 'Platform'),
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
