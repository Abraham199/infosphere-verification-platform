@extends('layouts.tenant')

@section('title', 'Verification History')

@section('content')
@include('tenant.verification.partials.nav')

<x-ui.card title="Search History" subtitle="Filter by reference or status.">
    <form method="GET" action="{{ route('tenant.verification.history', ['tenant' => $tenant]) }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <x-ui.input label="Reference" name="search" :value="$search" placeholder="VER-..." />
        </div>
        <div class="col-md-4">
            <x-ui.select label="Status" name="status" :selected="$status" :options="['' => 'All statuses', 'created' => 'Created', 'submitted' => 'Submitted', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled']" />
        </div>
        <div class="col-md-3">
            <x-ui.button type="submit" variant="primary" icon="fa-magnifying-glass">Search</x-ui.button>
        </div>
    </form>
</x-ui.card>

<x-ui.card class="mt-3" title="Verification Requests" subtitle="Tenant-scoped verification history.">
    <x-ui.table
        :headers="['Reference', 'Service', 'Amount', 'Status', 'Created']"
        :rows="$requests->map(fn ($request) => [
            '<a href=&quot;'.e(route('tenant.verification.show', ['tenant' => $tenant, 'reference' => $request->reference])).'&quot;>'.e($request->reference).'</a>',
            e($request->service?->name ?? '-'),
            e($request->currency.' '.number_format((float) $request->price_charged, 2)),
            view('components.ui.status-badge', ['status' => $request->status?->value ?? $request->status])->render(),
            e($request->created_at?->toDateTimeString() ?? '-'),
        ])->all()"
        empty="No matching verification requests found."
    />
</x-ui.card>
@endsection
