@props(['variant' => 'primary', 'size' => null, 'icon' => null, 'type' => 'button'])

@php
    $classes = trim('btn btn-'.$variant.' '.($size ? 'btn-'.$size : ''));
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)<i class="fa-solid {{ $icon }} me-2" aria-hidden="true"></i>@endif
    {{ $slot }}
</button>
