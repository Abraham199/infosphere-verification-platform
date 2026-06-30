@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb ivp-breadcrumb">
        @foreach($items as $label => $href)
            <li class="breadcrumb-item @if($loop->last) active @endif">
                @if($loop->last || ! $href)
                    {{ $label }}
                @else
                    <a href="{{ $href }}">{{ $label }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
