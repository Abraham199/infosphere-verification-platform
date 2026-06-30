@props(['title' => 'Nothing here yet', 'message' => null, 'icon' => 'fa-inbox', 'compact' => false])

<div {{ $attributes->merge(['class' => $compact ? 'ivp-empty ivp-empty-compact' : 'ivp-empty']) }}>
    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    <strong>{{ $title }}</strong>
    @if($message)<p>{{ $message }}</p>@endif
    {{ $slot }}
</div>
