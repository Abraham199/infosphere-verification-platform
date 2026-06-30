@props(['status' => 'pending'])

@php
    $normalized = strtolower((string) $status);
    $tone = match ($normalized) {
        'active', 'completed', 'sent', 'successful', 'success', 'published' => 'success',
        'failed', 'cancelled', 'inactive', 'disabled' => 'danger',
        'pending', 'draft', 'processing', 'retrying' => 'warning',
        default => 'secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge text-bg-'.$tone]) }}>{{ str($status)->headline() }}</span>
