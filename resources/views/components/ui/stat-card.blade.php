@props(['label', 'value', 'icon' => 'fa-chart-simple', 'tone' => 'primary', 'caption' => null])

<section {{ $attributes->merge(['class' => 'ivp-stat']) }}>
    <div class="ivp-stat-icon ivp-stat-{{ $tone }}">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
    </div>
    <div>
        <p class="ivp-stat-label">{{ $label }}</p>
        <strong class="ivp-stat-value">{{ $value }}</strong>
        @if($caption)<span class="ivp-stat-caption">{{ $caption }}</span>@endif
    </div>
</section>
