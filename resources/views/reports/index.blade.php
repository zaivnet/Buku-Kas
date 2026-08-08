<x-app-layout title="Laporan Keuangan Kas">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">
                Laporan Keuangan Kas
            </h1>
            <p class="text-sm text-neutral-muted mt-1">
                Laporan rinci arus kas masuk & keluar
                @if(auth()->user()->isStaff())
                    untuk {{ auth()->user()->outlet->name ?? 'Outlet Anda' }}.
                @else
                    seluruh outlet toko.
                @endif
            </p>
        </div>

        {{-- Export & Print Buttons --}}
        <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
            @php
                $queryParams = request()->only(['from', 'to', 'type', 'category_id', 'outlet_id']);
            @endphp

            {{-- Export Excel --}}
            <a href="{{ route('reports.export.excel', $queryParams) }}" class="btn-secondary text-xs">
                <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>

            {{-- Export PDF --}}
            <a href="{{ route('reports.export.pdf', $queryParams) }}" class="btn-secondary text-xs">
                <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>

            {{-- Cetak Browser --}}
            <a href="{{ route('reports.print', $queryParams) }}" target="_blank" class="btn-secondary text-xs">
                <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            {{-- Dari Tanggal --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="form-input text-xs" />
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="form-input text-xs" />
            </div>

            {{-- Tipe Transaksi --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Tipe Transaksi</label>
                <select name="type" class="form-input text-xs">
                    <option value="">Semua (Pemasukan & Pengeluaran)</option>
                    <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Pemasukan (Income)</option>
                    <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Pengeluaran (Expense)</option>
                </select>
            </div>

            {{-- Kategori --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Kategori</label>
                <select name="category_id" class="form-input text-xs">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                            [{{ strtoupper($cat->type->value) }}] {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Outlet --}}
            <div>
                <label class="text-xs font-semibold text-neutral-muted block mb-1">Outlet Toko</label>
                @if(auth()->user()->isStaff())
                    <input type="text" class="form-input text-xs bg-neutral-100 text-neutral-muted" value="{{ auth()->user()->outlet->name ?? '-' }}" disabled />
                @else
                    <select name="outlet_id" class="form-input text-xs">
                        <option value="">Semua Outlet</option>
                        @foreach($outlets as $out)
                            <option value="{{ $out->id }}" {{ $outletId == $out->id ? 'selected' : '' }}>{{ $out->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Submit & Reset --}}
            <div class="sm:col-span-2 md:col-span-5 flex items-center justify-end gap-2 pt-2 border-t border-neutral-border">
                <a href="{{ route('reports.index') }}" class="text-xs text-neutral-muted hover:text-neutral-text mr-auto">Reset Filter</a>
                <button type="submit" class="btn-primary text-xs px-4 py-1.5">Tampilkan Laporan</button>
            </div>
        </form>
    </div>

    {{-- Ringkasan Laporan Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-4 border-l-4 border-l-success-600 bg-white">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pemasukan</p>
            <h3 class="text-xl font-extrabold text-success-700 mt-1">
                Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
            </h3>
        </div>

        <div class="card p-4 border-l-4 border-l-danger-600 bg-white">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pengeluaran</p>
            <h3 class="text-xl font-extrabold text-danger-700 mt-1">
                Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}
            </h3>
        </div>

        <div class="card p-4 border-l-4 {{ $summary['balance'] >= 0 ? 'border-l-primary' : 'border-l-danger-600' }} bg-white">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Saldo Kas (Net)</p>
            <h3 class="text-xl font-extrabold {{ $summary['balance'] >= 0 ? 'text-primary' : 'text-danger-700' }} mt-1">
                Rp {{ number_format($summary['balance'], 0, ',', '.') }}
            </h3>
        </div>
    </div>

    {{-- Tabel Laporan Transaksi --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-bg text-neutral-muted text-xs uppercase tracking-wider border-b border-neutral-border">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Tanggal</th>
                        <th class="px-6 py-3 font-semibold">Tipe</th>
                        <th class="px-6 py-3 font-semibold">Kategori</th>
                        @if(!auth()->user()->isStaff())
                            <th class="px-6 py-3 font-semibold">Outlet</th>
                        @endif
                        <th class="px-6 py-3 font-semibold">Atas Nama</th>
                        <th class="px-6 py-3 font-semibold">Keterangan</th>
                        <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                        <th class="px-6 py-3 font-semibold text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-border bg-white">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-neutral-bg/50 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap font-medium text-neutral-text">
                                {{ $trx->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                @if($trx->type->value === 'income')
                                    <x-badge type="income">Pemasukan</x-badge>
                                @else
                                    <x-badge type="expense">Pengeluaran</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-neutral-text font-medium">
                                {{ $trx->category->name ?? '-' }}
                            </td>
                            @if(!auth()->user()->isStaff())
                                <td class="px-6 py-3.5 text-neutral-muted">
                                    {{ $trx->outlet->name ?? '-' }}
                                </td>
                            @endif
                            <td class="px-6 py-3.5 text-neutral-text">
                                {{ $trx->payer_name }}
                            </td>
                            <td class="px-6 py-3.5 text-neutral-muted text-xs truncate max-w-xs">
                                {{ $trx->description ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold whitespace-nowrap {{ $trx->type->value === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                                {{ $trx->formatted_amount }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                @if($trx->proof_image_url)
                                    <a href="{{ route('transactions.show', $trx) }}" class="text-xs text-primary hover:underline font-medium">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-xs text-neutral-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-neutral-muted">
                                Tidak ada transaksi yang sesuai dengan filter laporan.
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
</x-app-layout>
