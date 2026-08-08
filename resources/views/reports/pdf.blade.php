<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Kas</title>
    <style>
        @page {
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-card {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .summary-card.income { background-color: #ecfdf5; border-color: #a7f3d0; }
        .summary-card.expense { background-color: #fef2f2; border-color: #fecaca; }
        .summary-card.balance { background-color: #eff6ff; border-color: #bfdbfe; }

        .summary-label {
            font-size: 9px;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }
        .text-income { color: #047857; }
        .text-expense { color: #b91c1c; }
        .text-balance { color: #1d4ed8; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 8px;
            border: 1px solid #1e3a8a;
            text-align: left;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-income { background-color: #d1fae5; color: #065f46; }
        .badge-expense { background-color: #fee2e2; color: #991b1b; }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1 class="title">Buku Kas Digital</h1>
        <p class="subtitle">
            Laporan Keuangan Kas | Periode: {{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : '-' }} s.d. {{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : '-' }}
            @if($outletName) | Outlet: {{ $outletName }} @endif
        </p>
    </div>

    {{-- Summary Cards Table --}}
    <table class="summary-box">
        <tr>
            <td width="32%" style="padding-right: 8px;">
                <div class="summary-card income">
                    <div class="summary-label">Total Pemasukan</div>
                    <div class="summary-value text-income">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="32%" style="padding-right: 8px;">
                <div class="summary-card expense">
                    <div class="summary-label">Total Pengeluaran</div>
                    <div class="summary-value text-expense">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="36%">
                <div class="summary-card balance">
                    <div class="summary-label">Saldo Net (Kas)</div>
                    <div class="summary-value {{ $summary['balance'] >= 0 ? 'text-balance' : 'text-expense' }}">
                        Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="12%">Tanggal</th>
                <th width="12%">Tipe</th>
                <th width="15%">Kategori</th>
                <th width="15%">Outlet</th>
                <th width="16%">Atas Nama</th>
                <th width="14%" class="text-right">Nominal (Rp)</th>
                <th width="12%">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $idx => $trx)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $trx->date->format('d/m/Y') }}</td>
                    <td>
                        @if($trx->type->value === 'income')
                            <span class="badge badge-income">Pemasukan</span>
                        @else
                            <span class="badge badge-expense">Pengeluaran</span>
                        @endif
                    </td>
                    <td>{{ $trx->category->name ?? '-' }}</td>
                    <td>{{ $trx->outlet->name ?? '-' }}</td>
                    <td>{{ $trx->payer_name }}</td>
                    <td class="text-right font-bold {{ $trx->type->value === 'income' ? 'text-income' : 'text-expense' }}">
                        {{ $trx->formatted_amount }}
                    </td>
                    <td>{{ $trx->creator->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #9ca3af;">
                        Tidak ada catatan transaksi untuk periode dan filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Dicetak otomatis oleh Sistem Buku Kas Digital pada {{ now()->format('d/m/Y H:i') }} WIB
    </div>

</body>
</html>
