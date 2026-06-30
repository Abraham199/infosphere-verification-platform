@extends('layouts.guest')

@section('content')
<div class="ivp-auth-shell">
    <section class="ivp-auth-panel">
        <a class="ivp-brand" href="{{ route('home') }}">
            <span class="ivp-brand-mark"><i class="fa-solid fa-globe"></i></span>
            <span>Info-Sphere Technologies</span>
        </a>
        <div>
            <p class="text-white-50 mb-2">Infosphere Verification Portal</p>
            <h1 class="display-6 fw-bold">Secure access for identity operations.</h1>
            <p class="text-white-50 mb-0">Manage verification, wallet activity, products, reports, and support from one controlled workspace.</p>
        </div>
        <small class="text-white-50">Enterprise SaaS console</small>
    </section>

    <section class="ivp-auth-card">
        <x-ui.card title="Sign in" subtitle="Use your approved IVP account.">
            @if(session('status'))
                <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
            @endif
            <form method="POST" action="{{ route('login') }}" class="vstack gap-3">
                @csrf
                <div>
                    <x-ui.input label="Email address" name="email" type="email" :value="old('email')" autocomplete="email" required autofocus />
                </div>
                <div>
                    <x-ui.input label="Password" name="password" type="password" autocomplete="current-password" required />
                </div>
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <x-ui.checkbox label="Remember me" name="remember" />
                    <a class="small" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <x-ui.button type="submit" variant="primary" icon="fa-arrow-right-to-bracket">Login</x-ui.button>
            </form>
        </x-ui.card>
    </section>
</div>
@endsection
