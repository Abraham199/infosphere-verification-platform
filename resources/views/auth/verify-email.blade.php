@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="mx-auto" style="max-width: 620px;">
        <x-ui.card title="Verify your email" subtitle="Your account needs a confirmed email address before continuing.">
            @if(session('status') === 'verification-link-sent')
                <x-ui.alert variant="success" icon="fa-circle-check">A fresh verification link has been sent.</x-ui.alert>
            @endif
            <p class="ivp-muted">Check your inbox for the verification link. You can request another link if the first one expired.</p>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-ui.button type="submit" variant="primary" icon="fa-envelope">Resend email</x-ui.button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="outline-secondary" icon="fa-arrow-right-from-bracket">Logout</x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
