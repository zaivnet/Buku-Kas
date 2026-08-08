<div
    x-data="{ open: false }"
    x-on:open-about-modal.window="open = true"
    x-show="open"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    style="display: none;"
    x-on:keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200 transform opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150 transform opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-neutral-border"
        x-on:click.away="open = false"
    >
        {{-- Header Modal --}}
        <div class="bg-primary p-6 text-white text-center relative">
            <button
                type="button"
                x-on:click="open = false"
                class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors"
                aria-label="Tutup"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-white/20 shadow-inner">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>

            <h2 class="text-xl font-bold">Buku Kas Digital</h2>
            <span class="inline-block mt-1 px-3 py-0.5 bg-white/20 text-white text-xs rounded-full font-medium">
                Versi 1.0.0 (Produksi)
            </span>
        </div>

        {{-- Body Content --}}
        <div class="p-6 space-y-4 text-sm text-neutral-text">
            {{-- Deskripsi Singkat --}}
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-muted mb-1">Deskripsi Aplikasi</h4>
                <p class="text-neutral-text leading-relaxed text-xs">
                    Sistem manajemen keuangan & kas digital multi-outlet yang dirancang untuk memudahkan pencatatan arus uang masuk (pemasukan) dan uang keluar (pengeluaran), pemantauan saldo kas realtime, kompresi bukti nota/struk, serta pelaporan keuangan terpadu (Export Excel & PDF).
                </p>
            </div>

            {{-- Detail Pengembang --}}
            <div class="pt-3 border-t border-neutral-border grid grid-cols-1 gap-3">
                <div class="flex items-center gap-3 p-3 bg-neutral-bg rounded-xl border border-neutral-border">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center flex-shrink-0 font-bold">
                        AZ
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold">Pengembang (Developer)</p>
                        <p class="text-sm font-bold text-neutral-text">Ade Zaiv</p>
                    </div>
                </div>

                {{-- Kontak & Dukungan --}}
                <div class="flex items-center gap-3 p-3 bg-neutral-bg rounded-xl border border-neutral-border">
                    <div class="w-10 h-10 rounded-full bg-success-50 text-success-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold">Dukungan & Kontak Email</p>
                        <a href="mailto:admin@pehawe.me" class="text-sm font-bold text-primary hover:underline truncate block">
                            admin@pehawe.me
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="p-4 bg-neutral-bg border-t border-neutral-border flex justify-end">
            <button
                type="button"
                x-on:click="open = false"
                class="btn-primary text-xs px-5 py-2"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
