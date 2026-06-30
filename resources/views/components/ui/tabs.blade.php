@props(['tabs' => []])

<div class="ivp-tabs" role="tablist">
    @foreach($tabs as $tab)
        <a class="ivp-tab @if($tab['active'] ?? false) active @endif" href="{{ $tab['href'] ?? '#' }}">
            @if(isset($tab['icon']))<i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i>@endif
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>
