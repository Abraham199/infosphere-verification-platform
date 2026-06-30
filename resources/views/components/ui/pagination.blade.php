@props(['label' => 'Page navigation'])

<nav aria-label="{{ $label }}" class="ivp-pagination">
    {{ $slot }}
</nav>
