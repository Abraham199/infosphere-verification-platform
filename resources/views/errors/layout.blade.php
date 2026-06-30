<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
<main class="container py-5">
    <div class="ivp-card p-5 mx-auto" style="max-width: 640px;">
        <p class="text-primary fw-semibold mb-2">@yield('code')</p>
        <h1 class="h3">@yield('title')</h1>
        <p class="text-muted">@yield('message')</p>
        <a class="btn btn-ivp" href="{{ route('home') }}">Return Home</a>
    </div>
</main>
</body>
</html>
