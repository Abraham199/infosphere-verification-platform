<x-ui.tabs :tabs="[
    ['label' => 'Overview', 'href' => route('tenant.wallet.index', ['tenant' => $tenant]), 'icon' => 'fa-wallet', 'active' => request()->routeIs('tenant.wallet.index')],
    ['label' => 'Transactions', 'href' => route('tenant.wallet.transactions', ['tenant' => $tenant]), 'icon' => 'fa-list', 'active' => request()->routeIs('tenant.wallet.transactions')],
    ['label' => 'Funding', 'href' => route('tenant.wallet.funding.index', ['tenant' => $tenant]), 'icon' => 'fa-credit-card', 'active' => request()->routeIs('tenant.wallet.funding.*')],
    ['label' => 'Reservations', 'href' => route('tenant.wallet.reservations', ['tenant' => $tenant]), 'icon' => 'fa-lock', 'active' => request()->routeIs('tenant.wallet.reservations')],
]" />
