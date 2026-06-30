@extends('layouts.guest')

@section('content')
<div class="ivp-auth-shell">
    <section class="ivp-auth-panel">
        <a class="ivp-brand" href="{{ route('home') }}">
            <span class="ivp-brand-mark"><i class="fa-solid fa-globe"></i></span>
            <span>Info-Sphere</span>
        </a>
        <div>
            <h1 class="display-6 fw-bold">Set a new password.</h1>
            <p class="text-white-50">Choose a strong credential to continue using IVP.</p>
        </div>
        <small class="text-white-50">Protected account workflow</small>
    </section>
    <section class="ivp-auth-card">
        <x-ui.card title="Reset password" subtitle="Complete the secure password reset.">
            <form method="POST" action="{{ route('password.store') }}" class="vstack gap-3">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <x-ui.input label="Email address" name="email" type="email" :value="old('email', $request->email)" required />
                <x-ui.input label="New password" name="password" type="password" required />
                <x-ui.input label="Confirm password" name="password_confirmation" type="password" required />
                <x-ui.button type="submit" variant="primary" icon="fa-key">Reset password</x-ui.button>
            </form>
        </x-ui.card>
    </section>
</div>
@endsection
