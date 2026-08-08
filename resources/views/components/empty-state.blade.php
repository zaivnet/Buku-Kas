@props([
    'title' => 'Belum Ada Data',
    'description' => 'Belum ada catatan yang tersimpan di sistem saat ini.',
    'actionUrl' => null,
    'actionLabel' => null,
])

<div class="card p-12 text-center my-4 flex flex-col items-center justify-center">
    <div class="w-16 h-16 rounded-full bg-neutral-bg text-neutral-400 flex items-center justify-center mb-4 border border-neutral-border">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>

    <h3 class="text-base font-bold text-neutral-text">{{ $title }}</h3>
    <p class="text-xs text-neutral-muted max-w-sm mt-1 mb-6 leading-relaxed">{{ $description }}</p>

    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn-primary text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>
