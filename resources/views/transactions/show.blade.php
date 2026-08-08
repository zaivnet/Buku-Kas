<x-app-layout title="Detail Transaksi #{{ $transaction->id }}">
    <div class="max-w-3xl mx-auto" x-data="{ lightboxOpen: false }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-neutral-text">Detail Transaksi</h1>
                <p class="text-sm text-neutral-muted mt-1">Informasi rincian transaksi #{{ $transaction->id }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $transaction)
                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                @endcan

                <a href="{{ route($transaction->type->value === 'income' ? 'transactions.income' : 'transactions.expense') }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- Main Detail Card --}}
        <div class="card p-6 space-y-6">
            {{-- Nominal Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl {{ $transaction->type->value === 'income' ? 'bg-success-50 border border-success-100' : 'bg-danger-50 border border-danger-100' }}">
                <div>
                    <span class="text-xs uppercase tracking-wider font-bold text-neutral-muted">Total Nominal</span>
                    <h2 class="text-3xl font-extrabold {{ $transaction->type->value === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                        {{ $transaction->formatted_amount }}
                    </h2>
                </div>
                <div class="mt-2 sm:mt-0">
                    @if($transaction->type->value === 'income')
                        <x-badge type="income" class="text-sm px-3 py-1">Pemasukan (Income)</x-badge>
                    @else
                        <x-badge type="expense" class="text-sm px-3 py-1">Pengeluaran (Expense)</x-badge>
                    @endif
                </div>
            </div>

            {{-- Detail Info Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div>
                    <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-1">Tanggal Transaksi</p>
                    <p class="text-sm font-medium text-neutral-text">{{ $transaction->date->format('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-1">Atas Nama (Penyetor/Penerima)</p>
                    <p class="text-sm font-medium text-neutral-text">{{ $transaction->payer_name }}</p>
                </div>

                <div>
                    <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-1">Kategori</p>
                    <p class="text-sm font-medium text-neutral-text">{{ $transaction->category->name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-1">Outlet Toko</p>
                    <p class="text-sm font-medium text-neutral-text">{{ $transaction->outlet->name ?? '-' }}</p>
                </div>
            </div>

            {{-- Keterangan --}}
            @if($transaction->description)
                <div class="pt-4 border-t border-neutral-border">
                    <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-1">Keterangan Rincian</p>
                    <p class="text-sm text-neutral-text leading-relaxed whitespace-pre-line">{{ $transaction->description }}</p>
                </div>
            @endif

            {{-- Bukti Gambar --}}
            <div class="pt-4 border-t border-neutral-border">
                <p class="text-xs text-neutral-muted uppercase tracking-wider font-semibold mb-3">Bukti Gambar Transaksi</p>
                @if($transaction->proof_image_url)
                    <div class="relative inline-block group rounded-xl overflow-hidden border border-neutral-border cursor-pointer shadow-sm" x-on:click="lightboxOpen = true">
                        <img src="{{ $transaction->proof_image_url }}" alt="Bukti Transaksi #{{ $transaction->id }}" class="max-h-64 object-cover group-hover:opacity-90 transition-opacity" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-medium transition-opacity">
                            Klik untuk memperbesar
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-neutral-bg rounded-lg text-sm text-neutral-muted italic">
                        Tidak ada bukti gambar yang diunggah.
                    </div>
                @endif
            </div>

            {{-- Audit Trail (Recorded by & Last Updated by) --}}
            <div class="pt-4 border-t border-neutral-border grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-neutral-muted">
                <div>
                    <span class="font-medium text-neutral-text">Dicatat oleh:</span>
                    {{ $transaction->creator->name ?? 'Sistem' }} ({{ $transaction->created_at->format('d M Y, H:i') }})
                </div>
                @if($transaction->updater)
                    <div>
                        <span class="font-medium text-neutral-text">Terakhir diperbarui oleh:</span>
                        {{ $transaction->updater->name }} ({{ $transaction->updated_at->format('d M Y, H:i') }})
                    </div>
                @endif
            </div>
        </div>

        {{-- Lightbox Zoom Modal --}}
        @if($transaction->proof_image_url)
            <div
                x-show="lightboxOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
                style="display: none;"
                x-on:click="lightboxOpen = false"
                x-on:keydown.escape.window="lightboxOpen = false"
            >
                <div class="relative max-w-4xl max-h-[90vh]">
                    <img src="{{ $transaction->proof_image_url }}" alt="Bukti Transaksi Full" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain mx-auto" />
                    <p class="text-center text-white text-xs mt-2">Tekan ESC atau klik di mana saja untuk menutup</p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
