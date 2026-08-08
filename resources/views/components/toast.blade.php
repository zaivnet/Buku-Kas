<div
    x-data="{
        show: true,
        type: '{{ session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : (session('info') ? 'info' : ''))) }}',
        message: '{{ session('success') ?? session('error') ?? session('warning') ?? session('info') }}',
        init() {
            if (this.message) {
                setTimeout(() => {
                    this.show = false;
                }, 4000);
            }
        }
    }"
    x-show="show && message"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200 opacity-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-5 right-5 z-50 max-w-sm w-full shadow-lg rounded-xl p-4 border flex items-start gap-3 bg-white"
    :class="{
        'border-success-200 text-success-800 bg-success-50/90': type === 'success',
        'border-danger-200 text-danger-800 bg-danger-50/90': type === 'error',
        'border-warning-200 text-warning-800 bg-warning-50/90': type === 'warning',
        'border-primary/20 text-primary bg-primary/5': type === 'info'
    }"
    style="display: none;"
>
    {{-- Icon --}}
    <div class="flex-shrink-0 mt-0.5">
        <template x-if="type === 'success'">
            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <template x-if="type === 'error'">
            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <template x-if="type === 'warning'">
            <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </template>
        <template x-if="type === 'info'">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
    </div>

    {{-- Text --}}
    <div class="flex-1 text-sm font-medium">
        <p x-text="message"></p>
    </div>

    {{-- Close Button --}}
    <button type="button" x-on:click="show = false" class="flex-shrink-0 text-neutral-400 hover:text-neutral-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
