@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
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

    <input
        {{ $disabled ? 'disabled' : '' }}
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name ?? $attributes->get('id') }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input ' . ($errors->has($name) ? 'border-danger focus:border-danger focus:ring-danger' : '')]) }}
    />

    @if($hint)
        <p class="mt-1 text-xs text-neutral-muted">{{ $hint }}</p>
    @endif

    @if($name && $errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif
</div>
