@props([
    'type' => 'default', // success, danger, warning, primary, default
])

@php
$classes = match($type) {
    'success', 'income' => 'bg-success-100 text-success-700 border border-success-200',
    'danger', 'expense' => 'bg-danger-100 text-danger-700 border border-danger-200',
    'warning'           => 'bg-warning-100 text-warning-700 border border-warning-200',
    'primary'           => 'bg-primary-50 text-primary border border-primary-100',
    default             => 'bg-neutral-100 text-neutral-700 border border-neutral-200',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
