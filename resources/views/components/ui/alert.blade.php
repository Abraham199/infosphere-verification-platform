@props(['variant' => 'info', 'icon' => null])

<div {{ $attributes->merge(['class' => 'alert alert-'.$variant]) }} role="alert">
    @if($icon)<i class="fa-solid {{ $icon }} me-2" aria-hidden="true"></i>@endif
    {{ $slot }}
</div>
