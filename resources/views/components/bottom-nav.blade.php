<div
    x-data="{
        fabOpen: false
    }"
    class="sm:hidden"
>
    {{-- Bottom Navigation Bar --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-neutral-border shadow-lg px-2 py-1 flex items-center justify-around">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-3 text-xs font-medium {{ request()->routeIs('dashboard') ? 'text-primary font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Home</span>
        </a>

        {{-- Pemasukan --}}
        <a href="{{ route('transactions.income') }}" class="flex flex-col items-center py-1 px-3 text-xs font-medium {{ request()->routeIs('transactions.income') || (request()->routeIs('transactions.*') && request()->query('type') === 'income') ? 'text-success-600 font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
            <svg class="w-5 h-5 mb-0.5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Masuk</span>
        </a>

        {{-- Center Floating Action Button (FAB) + Catat --}}
        @can('create', App\Models\Transaction::class)
            <div class="relative -top-4">
                <button
                    type="button"
                    x-on:click="fabOpen = true"
                    class="w-13 h-13 rounded-full bg-primary text-white shadow-xl flex items-center justify-center border-4 border-white hover:scale-105 active:scale-95 transition-transform"
                    aria-label="Catat Transaksi Baru"
                >
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        @endcan

        {{-- Pengeluaran --}}
        <a href="{{ route('transactions.expense') }}" class="flex flex-col items-center py-1 px-3 text-xs font-medium {{ request()->routeIs('transactions.expense') || (request()->routeIs('transactions.*') && request()->query('type') === 'expense') ? 'text-danger-600 font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
            <svg class="w-5 h-5 mb-0.5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            <span>Keluar</span>
        </a>

        {{-- Laporan --}}
        <a href="{{ route('reports.index') }}" class="flex flex-col items-center py-1 px-3 text-xs font-medium {{ request()->routeIs('reports.*') ? 'text-primary font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Laporan</span>
        </a>
    </nav>

    {{-- Quick Action Bottom Sheet Modal --}}
    @can('create', App\Models\Transaction::class)
        <div
            x-show="fabOpen"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-sm"
            style="display: none;"
            x-on:click.self="fabOpen = false"
            x-on:keydown.escape.window="fabOpen = false"
        >
            <div
                x-show="fabOpen"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="w-full bg-white rounded-t-3xl p-6 space-y-4 shadow-2xl"
            >
                {{-- Sheet Handle --}}
                <div class="w-12 h-1.5 bg-neutral-300 rounded-full mx-auto mb-2"></div>

                <div class="text-center">
                    <h3 class="text-lg font-bold text-neutral-text">Pilih Transaksi Baru</h3>
                    <p class="text-xs text-neutral-muted mt-1">Pilih jenis catatan keuangan yang ingin ditambahkan</p>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    {{-- Catat Pemasukan --}}
                    <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-success-50 border border-success-200 text-success-700 hover:bg-success-100 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-success-600 text-white flex items-center justify-center mb-2 shadow">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="font-bold text-sm">Pemasukan</span>
                        <span class="text-[10px] text-success-600 mt-0.5">Uang Masuk / Omset</span>
                    </a>

                    {{-- Catat Pengeluaran --}}
                    <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-danger-50 border border-danger-200 text-danger-700 hover:bg-danger-100 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-danger-600 text-white flex items-center justify-center mb-2 shadow">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </div>
                        <span class="font-bold text-sm">Pengeluaran</span>
                        <span class="text-[10px] text-danger-600 mt-0.5">Uang Keluar / Biaya</span>
                    </a>
                </div>

                <button type="button" x-on:click="fabOpen = false" class="w-full py-3 text-center text-xs font-semibold text-neutral-500 hover:text-neutral-800">
                    Batal
                </button>
            </div>
        </div>
    @endcan
</div>
