<x-app-layout title="Dashboard Utama">
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="{{ asset('js/dashboard.js') }}" defer></script>
    @endpush

    {{-- Header & Filter Periode --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">
                Dashboard Ringkasan Kas
            </h1>
            <p class="text-sm text-neutral-muted mt-1">
                Ikhtisar arus keuangan {{ auth()->user()->isStaff() ? 'untuk ' . (auth()->user()->outlet->name ?? 'Outlet Anda') : 'seluruh outlet toko' }}.
            </p>
        </div>

        {{-- Filter Bar Ringkas --}}
        <div class="card p-3 bg-white">
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <div class="flex items-center gap-1">
                    <span class="font-medium text-neutral-muted">Dari:</span>
                    <input type="date" name="from" value="{{ $from }}" class="form-input py-1 text-xs" />
                </div>
                <div class="flex items-center gap-1">
                    <span class="font-medium text-neutral-muted">Sampai:</span>
                    <input type="date" name="to" value="{{ $to }}" class="form-input py-1 text-xs" />
                </div>

                @if(!auth()->user()->isStaff())
                    <div class="flex items-center gap-1">
                        <span class="font-medium text-neutral-muted">Outlet:</span>
                        <select name="outlet_id" class="form-input py-1 text-xs">
                            <option value="">Semua Outlet</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->id }}" {{ $outletId == $out->id ? 'selected' : '' }}>{{ $out->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit" class="btn-primary text-xs py-1 px-3">Terapkan</button>
            </form>
        </div>
    </div>

    {{-- 3 KARTU RINGKASAN SALDO --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        {{-- Total Pemasukan --}}
        <div class="card p-5 border-l-4 border-l-success-600 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pemasukan</p>
                <h3 class="text-2xl font-extrabold text-success-700 mt-1">
                    Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-neutral-400 mt-1">Periode {{ \Carbon\Carbon::parse($from)->format('d M') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-success-50 text-success-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="card p-5 border-l-4 border-l-danger-600 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pengeluaran</p>
                <h3 class="text-2xl font-extrabold text-danger-700 mt-1">
                    Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-neutral-400 mt-1">Periode {{ \Carbon\Carbon::parse($from)->format('d M') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-danger-50 text-danger-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </div>
        </div>

        {{-- Saldo Bersih --}}
        <div class="card p-5 border-l-4 {{ $summary['balance'] >= 0 ? 'border-l-primary' : 'border-l-danger-600' }} flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Saldo Bersih (Kas)</p>
                <h3 class="text-2xl font-extrabold {{ $summary['balance'] >= 0 ? 'text-primary' : 'text-danger-700' }} mt-1">
                    Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                </h3>
                <p class="text-xs text-neutral-400 mt-1">Surplus / (Defisit) Bersih</p>
            </div>
            <div class="w-12 h-12 rounded-xl {{ $summary['balance'] >= 0 ? 'bg-primary/10 text-primary' : 'bg-danger-50 text-danger-600' }} flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- GRAFIK SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Trend Line Chart --}}
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-neutral-text">Grafik Tren Keuangan</h3>
                <span class="text-xs text-neutral-muted">Pemasukan vs Pengeluaran</span>
            </div>
            <div class="relative h-72">
                <canvas
                    id="trendChart"
                    data-labels="{{ json_encode($trend['labels']) }}"
                    data-income="{{ json_encode($trend['income']) }}"
                    data-expense="{{ json_encode($trend['expense']) }}"
                ></canvas>
            </div>
        </div>

        {{-- Category Breakdown Doughnut Chart --}}
        <div class="card p-5">
            <h3 class="text-base font-bold text-neutral-text mb-1">Proporsi Kategori</h3>
            <p class="text-xs text-neutral-muted mb-4">Breakdown Pengeluaran Kas</p>

            <div class="relative h-60">
                @if(count($expenseBreakdown['labels']) > 0)
                    <canvas
                        id="expenseCategoryChart"
                        data-labels="{{ json_encode($expenseBreakdown['labels']) }}"
                        data-values="{{ json_encode($expenseBreakdown['values']) }}"
                    ></canvas>
                @else
                    <div class="h-full flex items-center justify-center text-xs text-neutral-400 italic">
                        Belum ada data pengeluaran.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABEL 10 TRANSAKSI TERBARU --}}
    <div class="card overflow-hidden">
        <div class="p-5 border-b border-neutral-border flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-text">Catatan Transaksi Terbaru</h3>
                <p class="text-xs text-neutral-muted mt-0.5">10 transaksi terakhir yang baru saja dicatat.</p>
            </div>
            <a href="{{ route('transactions.income') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

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
                        <th class="px-6 py-3 font-semibold text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-border bg-white">
                    @forelse($latestTransactions as $trx)
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
                            <td class="px-6 py-3.5 text-neutral-text">
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
                            <td class="px-6 py-3.5 text-right font-bold whitespace-nowrap {{ $trx->type->value === 'income' ? 'text-success-700' : 'text-danger-700' }}">
                                {{ $trx->formatted_amount }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-neutral-muted text-sm">
                                Belum ada transaksi tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
