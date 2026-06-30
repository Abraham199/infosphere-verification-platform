@props(['label' => 'Loading'])

<div {{ $attributes->merge(['class' => 'ivp-loading']) }} aria-live="polite">
    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
    <span>{{ $label }}</span>
</div>
