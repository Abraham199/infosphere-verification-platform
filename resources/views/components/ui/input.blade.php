@props(['label', 'name', 'type' => 'text', 'value' => null, 'placeholder' => null])

<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $attributes->merge(['class' => 'form-control']) }}>
@error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
