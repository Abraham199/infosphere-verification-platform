<x-ui.tabs :tabs="[
    ['label' => 'Request', 'href' => route('tenant.verification.index', ['tenant' => $tenant]), 'icon' => 'fa-id-card', 'active' => request()->routeIs('tenant.verification.index')],
    ['label' => 'Services', 'href' => route('tenant.verification.services', ['tenant' => $tenant]), 'icon' => 'fa-list-check', 'active' => request()->routeIs('tenant.verification.services')],
    ['label' => 'History', 'href' => route('tenant.verification.history', ['tenant' => $tenant]), 'icon' => 'fa-clock-rotate-left', 'active' => request()->routeIs('tenant.verification.history')],
]" />
