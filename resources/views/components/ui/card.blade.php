@props(['title' => null, 'subtitle' => null, 'actions' => null])

<section {{ $attributes->merge(['class' => 'ivp-card']) }}>
    @if($title || $subtitle || $actions)
        <div class="ivp-card-header">
            <div>
                @if($title)<h2 class="ivp-card-title">{{ $title }}</h2>@endif
                @if($subtitle)<p class="ivp-card-subtitle">{{ $subtitle }}</p>@endif
            </div>
            @if($actions)<div class="ivp-card-actions">{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="ivp-card-body">
        {{ $slot }}
    </div>
</section>
