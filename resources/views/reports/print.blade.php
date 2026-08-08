<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Keuangan - Buku Kas Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px; }
            .card { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 p-8 font-sans" onload="window.print()">

    {{-- Top Action Bar (hidden when printing) --}}
    <div class="no-print max-w-5xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h2 class="font-bold text-gray-800">Pratinjau Cetak Laporan</h2>
            <p class="text-xs text-gray-500">Dialog cetak browser akan otomatis terbuka. Klik tombol di kanan jika belum.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow">
                Cetak Sekarang
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold">
                Tutup Window
            </button>
        </div>
    </div>

    {{-- Printable Container --}}
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl border border-gray-200 shadow-sm print:p-0 print:border-none print:shadow-none">
        {{-- Header Judul --}}
        <div class="border-b-2 border-blue-900 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-blue-900 uppercase tracking-wide">Buku Kas Digital</h1>
                    <p class="text-xs text-gray-600 mt-1">Laporan Rinci Arus Kas Keuangan Toko</p>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <p><span class="font-semibold">Tanggal Cetak:</span> {{ now()->format('d/m/Y H:i') }} WIB</p>
                    <p><span class="font-semibold">Dicetak Oleh:</span> {{ auth()->user()->name }}</p>
                </div>
            </div>

            {{-- Filter Summary --}}
            <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-gray-600">
                <div><span class="font-semibold">Periode:</span> {{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : '-' }} - {{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : '-' }}</div>
                <div><span class="font-semibold">Tipe:</span> {{ $type ? ucfirst($type) : 'Semua Tipe' }}</div>
                <div><span class="font-semibold">Outlet:</span> {{ $outletName ?? 'Semua Outlet' }}</div>
                <div><span class="font-semibold">Total Catatan:</span> {{ count($transactions) }} data</div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                <p class="text-xs font-semibold text-emerald-800 uppercase">Total Pemasukan</p>
                <p class="text-lg font-bold text-emerald-700 mt-1">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-lg bg-rose-50 border border-rose-200">
                <p class="text-xs font-semibold text-rose-800 uppercase">Total Pengeluaran</p>
                <p class="text-lg font-bold text-rose-700 mt-1">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
                <p class="text-xs font-semibold text-blue-800 uppercase">Saldo Net Kas</p>
                <p class="text-lg font-bold {{ $summary['balance'] >= 0 ? 'text-blue-700' : 'text-rose-700' }} mt-1">
                    Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Table --}}
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-blue-900 text-white uppercase text-[10px] tracking-wider">
                    <th class="p-2 border border-blue-900 text-center">No</th>
                    <th class="p-2 border border-blue-900">Tanggal</th>
                    <th class="p-2 border border-blue-900">Tipe</th>
                    <th class="p-2 border border-blue-900">Kategori</th>
                    <th class="p-2 border border-blue-900">Outlet</th>
                    <th class="p-2 border border-blue-900">Atas Nama</th>
                    <th class="p-2 border border-blue-900">Keterangan</th>
                    <th class="p-2 border border-blue-900 text-right">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $idx => $trx)
                    <tr class="{{ $idx % 2 === 1 ? 'bg-gray-50' : '' }}">
                        <td class="p-2 border border-gray-200 text-center">{{ $idx + 1 }}</td>
                        <td class="p-2 border border-gray-200 font-medium whitespace-nowrap">{{ $trx->date->format('d/m/Y') }}</td>
                        <td class="p-2 border border-gray-200 whitespace-nowrap">
                            @if($trx->type->value === 'income')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Pemasukan</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">Pengeluaran</span>
                            @endif
                        </td>
                        <td class="p-2 border border-gray-200">{{ $trx->category->name ?? '-' }}</td>
                        <td class="p-2 border border-gray-200">{{ $trx->outlet->name ?? '-' }}</td>
                        <td class="p-2 border border-gray-200 font-medium">{{ $trx->payer_name }}</td>
                        <td class="p-2 border border-gray-200 text-gray-600 truncate max-w-xs">{{ $trx->description ?? '-' }}</td>
                        <td class="p-2 border border-gray-200 text-right font-bold whitespace-nowrap {{ $trx->type->value === 'income' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $trx->formatted_amount }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-400 italic">Tidak ada catatan transaksi untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer Signatures --}}
        <div class="mt-12 pt-6 grid grid-cols-2 gap-8 text-center text-xs text-gray-600">
            <div>
                <p>Mengetahui / Penanggung Jawab,</p>
                <div class="h-16"></div>
                <p class="font-bold underline text-gray-800">( ............................................ )</p>
            </div>
            <div>
                <p>Petugas Kasir / Admin,</p>
                <div class="h-16"></div>
                <p class="font-bold underline text-gray-800">{{ auth()->user()->name }}</p>
            </div>
        </div>
    </div>

</body>
</html>
