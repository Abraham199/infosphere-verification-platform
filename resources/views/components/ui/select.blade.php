@props(['label', 'name', 'options' => [], 'selected' => null])

<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'form-select']) }}>
    @foreach($options as $value => $text)
        <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $text }}</option>
    @endforeach
</select>
@error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
