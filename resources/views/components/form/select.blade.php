@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required) <span class="text-danger-600">*</span> @endif
        </label>
    @endif

    <select
        {{ $disabled ? 'disabled' : '' }}
        name="{{ $name }}"
        id="{{ $name ?? $attributes->get('id') }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-input ' . ($errors->has($name) ? 'border-danger focus:border-danger focus:ring-danger' : '')]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($hint)
        <p class="mt-1 text-xs text-neutral-muted">{{ $hint }}</p>
    @endif

    @if($name && $errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif
</div>
