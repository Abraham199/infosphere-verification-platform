@props(['title' => 'Notification', 'message' => null, 'variant' => 'info'])

<div {{ $attributes->merge(['class' => 'ivp-toast ivp-toast-'.$variant]) }} role="status">
    <strong>{{ $title }}</strong>
    @if($message)<span>{{ $message }}</span>@endif
    {{ $slot }}
</div>
