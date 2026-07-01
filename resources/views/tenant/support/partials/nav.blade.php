<x-ui.tabs :tabs="[
    ['label' => 'Tickets', 'href' => route('tenant.support.index', ['tenant' => $tenant]), 'icon' => 'fa-life-ring', 'active' => request()->routeIs('tenant.support.index')],
]" />
