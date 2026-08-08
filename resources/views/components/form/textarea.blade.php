@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'value' => null,
    'rows' => 3,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required) <span class="text-danger-600">*</span> @endif
        </label>
    @endif

    <textarea
        {{ $disabled ? 'disabled' : '' }}
        name="{{ $name }}"
        id="{{ $name ?? $attributes->get('id') }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input ' . ($errors->has($name) ? 'border-danger focus:border-danger focus:ring-danger' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @if($hint)
        <p class="mt-1 text-xs text-neutral-muted">{{ $hint }}</p>
    @endif

    @if($name && $errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif
</div>
