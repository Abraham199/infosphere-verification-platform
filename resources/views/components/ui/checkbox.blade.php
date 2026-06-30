@props(['label', 'name', 'checked' => false])

<div class="form-check">
    <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1" @checked(old($name, $checked)) {{ $attributes->merge(['class' => 'form-check-input']) }}>
    <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
</div>
