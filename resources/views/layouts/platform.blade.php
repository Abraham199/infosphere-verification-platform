<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="ivp-app-shell">
    <aside class="ivp-sidebar">
        <a class="ivp-brand" href="{{ route('platform.dashboard') }}">
            <span class="ivp-brand-mark"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>
            <span>IVP Platform</span>
        </a>
        <nav class="nav flex-column gap-1" aria-label="Platform navigation">
            <a class="nav-link @if(request()->routeIs('platform.dashboard')) active @endif" href="{{ route('platform.dashboard') }}"><i class="fa-solid fa-chart-line"></i>Overview</a>
            <a class="nav-link @if(request()->routeIs('platform.wallet.*')) active @endif" href="{{ route('platform.wallet.index') }}"><i class="fa-solid fa-wallet"></i>Wallets</a>
            <a class="nav-link @if(request()->routeIs('platform.verification.*')) active @endif" href="{{ route('platform.verification.index') }}"><i class="fa-solid fa-id-card"></i>Verifications</a>
            <a class="nav-link @if(request()->routeIs('platform.products.*')) active @endif" href="{{ route('platform.products.index') }}"><i class="fa-solid fa-boxes-stacked"></i>Products</a>
            <a class="nav-link @if(request()->routeIs('platform.support.*')) active @endif" href="{{ route('platform.support.index') }}"><i class="fa-solid fa-life-ring"></i>Support</a>
        </nav>
    </aside>
    <main class="ivp-main">
        <header class="ivp-topbar">
            <div>
                <p class="ivp-page-eyebrow mb-1">Platform Administration</p>
                <h1 class="ivp-page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="outline-secondary" size="sm" icon="fa-arrow-right-from-bracket">Logout</x-ui.button>
            </form>
        </header>

        @if(session('status'))
            <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
