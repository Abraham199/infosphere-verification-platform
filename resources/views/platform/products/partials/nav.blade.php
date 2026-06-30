<x-ui.tabs :tabs="[
    ['label' => 'Products', 'href' => route('platform.products.index'), 'icon' => 'fa-boxes-stacked', 'active' => request()->routeIs('platform.products.*')],
    ['label' => 'Categories', 'href' => route('platform.categories.index'), 'icon' => 'fa-layer-group', 'active' => request()->routeIs('platform.categories.*')],
    ['label' => 'Provider Mappings', 'href' => route('platform.provider-mappings.index'), 'icon' => 'fa-plug', 'active' => request()->routeIs('platform.provider-mappings.*')],
    ['label' => 'Tenant Overrides', 'href' => route('platform.tenant-products.index'), 'icon' => 'fa-building-user', 'active' => request()->routeIs('platform.tenant-products.*')],
]" />
