<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenant') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
@php($tenantParam = ['tenant' => $tenant])
<div class="ivp-app-shell">
    <aside class="ivp-sidebar">
        <a class="ivp-brand" href="{{ route('tenant.dashboard', $tenantParam) }}">
            <span class="ivp-brand-mark"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
            <span>{{ $tenant->name ?? 'Tenant' }}</span>
        </a>
        <nav class="nav flex-column gap-1" aria-label="Tenant navigation">
            <a class="nav-link @if(request()->routeIs('tenant.dashboard')) active @endif" href="{{ route('tenant.dashboard', $tenantParam) }}"><i class="fa-solid fa-border-all"></i>Dashboard</a>
            <a class="nav-link @if(request()->routeIs('tenant.wallet.*')) active @endif" href="{{ route('tenant.wallet.index', $tenantParam) }}"><i class="fa-solid fa-wallet"></i>Wallet</a>
            <a class="nav-link @if(request()->routeIs('tenant.verification.*')) active @endif" href="{{ route('tenant.verification.index', $tenantParam) }}"><i class="fa-solid fa-id-card"></i>Verification</a>
            <a class="nav-link @if(request()->routeIs('tenant.products.*')) active @endif" href="{{ route('tenant.products.index', $tenantParam) }}"><i class="fa-solid fa-box"></i>Products</a>
            <a class="nav-link @if(request()->routeIs('tenant.customer.*')) active @endif" href="{{ route('tenant.customer.dashboard', $tenantParam) }}"><i class="fa-solid fa-user"></i>Customer</a>
        </nav>
    </aside>
    <main class="ivp-main">
        <header class="ivp-topbar">
            <div>
                <p class="ivp-page-eyebrow mb-1">{{ $tenant->name ?? 'Tenant Workspace' }}</p>
                <h1 class="ivp-page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="outline-secondary" size="sm" icon="fa-arrow-right-from-bracket">Logout</x-ui.button>
            </form>
        </header>

        @yield('content')
    </main>
</div>
</body>
</html>
