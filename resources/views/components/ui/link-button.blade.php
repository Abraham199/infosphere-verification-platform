@props(['href' => '#', 'variant' => 'primary', 'size' => null, 'icon' => null])

@php
    $classes = trim('btn btn-'.$variant.' '.($size ? 'btn-'.$size : ''));
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)<i class="fa-solid {{ $icon }} me-2" aria-hidden="true"></i>@endif
    {{ $slot }}
</a>
