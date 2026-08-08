<x-app-layout title="Edit {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
    <div class="max-w-2xl mx-auto" x-data="{ submitting: false }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-neutral-text">
                    Edit {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                </h1>
                <p class="text-sm text-neutral-muted mt-1">Perbarui data transaksi {{ $transaction->formatted_amount }}.</p>
            </div>
            <a href="{{ route($type === 'income' ? 'transactions.income' : 'transactions.expense') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        {{-- Card Form --}}
        <div class="card p-6">
            <form action="{{ route('transactions.update', $transaction) }}" method="POST" enctype="multipart/form-data" x-on:submit="submitting = true">
                @method('PUT')
                @include('transactions.form')

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-neutral-border">
                    <a href="{{ route($type === 'income' ? 'transactions.income' : 'transactions.expense') }}" class="btn-secondary" :class="{ 'pointer-events-none opacity-50': submitting }">Batal</a>
                    <button type="submit" class="btn-primary" :disabled="submitting">
                        <template x-if="!submitting">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Perubahan
                            </span>
                        </template>
                        <template x-if="submitting">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
