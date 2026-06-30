@extends('layouts.tenant')

@section('title', 'Products')

@section('content')
<div class="ivp-grid-2">
    <x-ui.card title="Products" subtitle="Products available through the central catalog.">
        <x-ui.table
            :headers="['Code', 'Name', 'Price', 'Status', 'Visibility']"
            :rows="collect($products)->map(fn ($row) => [
                e($row['code'] ?? '-'),
                e($row['name'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['default_price'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'draft'])->render(),
                e(str($row['visibility'] ?? '-')->headline()),
            ])->all()"
            empty="No products configured."
        />
    </x-ui.card>

    <x-ui.card title="Categories" subtitle="Product grouping.">
        <x-ui.table
            :headers="['Code', 'Name', 'Status']"
            :rows="collect($categories)->map(fn ($row) => [
                e($row['code'] ?? '-'),
                e($row['name'] ?? '-'),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'active'])->render(),
            ])->all()"
            empty="No categories configured."
        />
    </x-ui.card>
</div>

<div class="ivp-grid-2 mt-3">
    <x-ui.card title="Provider Mappings" subtitle="Provider-neutral product routing.">
        <x-ui.table
            :headers="['Provider', 'Provider Code', 'Priority', 'Status']"
            :rows="collect($mappings)->map(fn ($row) => [
                e($row['provider'] ?? '-'),
                e($row['provider_product_code'] ?? '-'),
                e($row['priority'] ?? '-'),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'active'])->render(),
            ])->all()"
            empty="No provider mappings configured."
        />
    </x-ui.card>

    <x-ui.card title="Tenant Overrides" subtitle="Workspace-specific access and pricing.">
        <x-ui.table
            :headers="['Enabled', 'Price Override', 'Status Override']"
            :rows="collect($overrides)->map(fn ($row) => [
                ($row['is_enabled'] ?? false) ? 'Yes' : 'No',
                e($row['selling_price_override'] ?? '-'),
                e($row['status_override'] ?? '-'),
            ])->all()"
            empty="No tenant overrides configured."
        />
    </x-ui.card>
</div>
@endsection
