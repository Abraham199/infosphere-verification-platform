@props(['id', 'title'])

<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}Label">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title fs-5" id="{{ $id }}Label">{{ $title }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">{{ $slot }}</div>
</div>
