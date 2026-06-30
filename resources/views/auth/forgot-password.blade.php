@extends('layouts.guest')

@section('content')
<div class="ivp-auth-shell">
    <section class="ivp-auth-panel">
        <a class="ivp-brand" href="{{ route('home') }}">
            <span class="ivp-brand-mark"><i class="fa-solid fa-globe"></i></span>
            <span>Info-Sphere</span>
        </a>
        <div>
            <h1 class="display-6 fw-bold">Recover access securely.</h1>
            <p class="text-white-50">Password reset links are delivered through the configured mail channel.</p>
        </div>
        <a class="text-white-50" href="{{ route('login') }}">Back to login</a>
    </section>
    <section class="ivp-auth-card">
        <x-ui.card title="Forgot password" subtitle="Enter your email address to receive a reset link.">
            @if(session('status'))
                <x-ui.alert variant="success" icon="fa-circle-check">{{ session('status') }}</x-ui.alert>
            @endif
            <form method="POST" action="{{ route('password.email') }}" class="vstack gap-3">
                @csrf
                <x-ui.input label="Email address" name="email" type="email" :value="old('email')" required autofocus />
                <x-ui.button type="submit" variant="primary" icon="fa-paper-plane">Send reset link</x-ui.button>
            </form>
        </x-ui.card>
    </section>
</div>
@endsection
