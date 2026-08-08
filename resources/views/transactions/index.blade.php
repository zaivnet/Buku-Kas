<x-app-layout title="Manajemen {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
    {{-- Header & Button Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">
                {{ $type === 'income' ? 'Pemasukan (Income)' : 'Pengeluaran (Expense)' }}
            </h1>
            <p class="text-sm text-neutral-muted mt-1">
                Daftar catatan transaksi {{ $type === 'income' ? 'uang masuk' : 'uang keluar' }}
                @if(auth()->user()->isStaff())
                    untuk {{ auth()->user()->outlet->name ?? 'Outlet Anda' }}.
                @else
                    seluruh outlet.
                @endif
            </p>
        </div>
        @can('create', App\Models\Transaction::class)
            <a href="{{ route('transactions.create', ['type' => $type]) }}" class="btn-primary flex-shrink-0 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
            </a>
        @endcan
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route($type === 'income' ? 'transactions.income' : 'transactions.expense') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="hidden" name="type" value="{{ $type }}" />

            {{-- Dari Tanggal --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="form-input" />
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="form-input" />
            </div>

            {{-- Kategori --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Kategori</label>
                <select name="category_id" class="form-input">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Outlet (Terkunci untuk Staff) --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Outlet Toko</label>
                @if(auth()->user()->isStaff())
                    <input type="text" class="form-input bg-neutral-100 text-neutral-muted" value="{{ auth()->user()->outlet->name ?? '-' }}" disabled />
                @else
                    <select name="outlet_id" class="form-input">
                        <option value="">Semua Outlet</option>
                        @foreach($outlets as $out)
                            <option value="{{ $out->id }}" {{ $outletId == $out->id ? 'selected' : '' }}>{{ $out->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Search & Submit --}}
            <div class="flex items-end gap-2">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Atas nama / rincian..." class="form-input" />
                </div>
                <button type="submit" class="btn-secondary">Filter</button>
                @if($from || $to || $categoryId || ($outletId && !auth()->user()->isStaff()) || $search)
                    <a href="{{ route($type === 'income' ? 'transactions.income' : 'transactions.expense') }}" class="text-xs text-neutral-muted hover:text-neutral-text self-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Ringkasan Total Card --}}
    <div class="mb-4 flex items-center justify-between p-4 bg-white rounded-xl border border-neutral-border shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $type === 'income' ? 'bg-success-50 text-success-600' : 'bg-danger-50 text-danger-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($type === 'income')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    @endif
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-neutral-muted uppercase tracking-wider">Total {{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }} (Terfilter)</p>
                <p class="text-xl font-bold {{ $type === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </p>
            </div>
        </div>
        <span class="text-xs text-neutral-muted">Menampilkan {{ $transactions->total() }} catatan</span>
    </div>

    {{-- DESKTOP TABLE VIEW (sm ke atas) --}}
    <div class="card overflow-hidden hidden sm:block mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-bg text-neutral-muted text-xs uppercase tracking-wider border-b border-neutral-border">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Tanggal</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        @if(!auth()->user()->isStaff())
                            <th class="px-6 py-3 font-semibold">Outlet</th>
                        @endif
                        <th class="px-6 py-3 font-semibold">Atas Nama</th>
                        <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                        <th class="px-6 py-3 font-semibold text-center">Bukti</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-border bg-white">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-neutral-bg/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-neutral-text font-medium">
                                {{ $trx->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-text">{{ $trx->category->name ?? '-' }}</span>
                            </td>
                            @if(!auth()->user()->isStaff())
                                <td class="px-6 py-4 text-neutral-muted">
                                    {{ $trx->outlet->name ?? '-' }}
                                </td>
                            @endif
                            <td class="px-6 py-4 text-neutral-text">
                                <p class="font-medium">{{ $trx->payer_name }}</p>
                                @if($trx->description)
                                    <p class="text-xs text-neutral-muted truncate max-w-xs">{{ $trx->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold whitespace-nowrap {{ $type === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                                {{ $trx->formatted_amount }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($trx->proof_image_url)
                                    <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center gap-1 text-xs text-primary hover:underline font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Ada
                                    </a>
                                @else
                                    <span class="text-xs text-neutral-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Detail --}}
                                    <a href="{{ route('transactions.show', $trx) }}" class="p-1.5 text-neutral-muted hover:text-neutral-text rounded hover:bg-neutral-bg transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    {{-- Edit --}}
                                    @can('update', $trx)
                                        <a href="{{ route('transactions.edit', $trx) }}" class="p-1.5 text-neutral-muted hover:text-primary rounded hover:bg-neutral-bg transition-colors" title="Edit Transaksi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endcan

                                    {{-- Delete --}}
                                    @can('delete', $trx)
                                        <form action="{{ route('transactions.destroy', $trx) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-neutral-muted hover:text-danger rounded hover:bg-neutral-bg transition-colors" title="Hapus Transaksi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        @if(auth()->user()->isStaff() && (new App\Policies\TransactionPolicy)->isLockedForStaff($trx))
                                            <span class="p-1.5 text-neutral-300 cursor-not-allowed" title="Transaksi yang sudah lewat dari 7 hari terkunci dan hanya dapat diedit/dihapus oleh Admin.">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-neutral-muted">
                                <svg class="w-12 h-12 mx-auto text-neutral-border mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Belum ada catatan transaksi {{ $type === 'income' ? 'pemasukan' : 'pengeluaran' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-neutral-border bg-neutral-bg/30">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    {{-- MOBILE CARD LIST VIEW (sm ke bawah) --}}
    <div class="space-y-3 sm:hidden mb-6">
        @forelse($transactions as $trx)
            <div class="card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-muted">{{ $trx->date->format('d M Y') }}</span>
                    <span class="text-base font-bold {{ $type === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                        {{ $trx->formatted_amount }}
                    </span>
                </div>

                <div class="border-t border-neutral-border pt-2 text-sm space-y-1">
                    <p class="font-semibold text-neutral-text">{{ $trx->payer_name }}</p>
                    <div class="flex items-center gap-2 text-xs text-neutral-muted">
                        <span>{{ $trx->category->name ?? '-' }}</span>
                        @if(!auth()->user()->isStaff())
                            <span>&bull;</span>
                            <span>{{ $trx->outlet->name ?? '-' }}</span>
                        @endif
                    </div>
                    @if($trx->description)
                        <p class="text-xs text-neutral-muted italic pt-1">{{ $trx->description }}</p>
                    @endif
                </div>

                <div class="border-t border-neutral-border pt-2 flex items-center justify-between text-xs">
                    @if($trx->proof_image_url)
                        <a href="{{ route('transactions.show', $trx) }}" class="text-primary font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Bukti Gambar
                        </a>
                    @else
                        <span class="text-neutral-400">Tanpa Bukti</span>
                    @endif

                    <div class="flex items-center gap-2">
                        <a href="{{ route('transactions.show', $trx) }}" class="text-neutral-muted hover:text-neutral-text">Detail</a>
                        @can('update', $trx)
                            <a href="{{ route('transactions.edit', $trx) }}" class="text-primary font-medium">Edit</a>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-neutral-muted text-sm">
                Belum ada transaksi.
            </div>
        @endforelse

        @if($transactions->hasPages())
            <div class="p-4 card">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
