<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Generate and stream Excel file for filtered transactions.
     */
    public function export(array $filters, User $actor): StreamedResponse
    {
        // 1. Ambil data transaksi (tanpa pagination)
        $query = $this->reportService->getReportTransactions($filters, $actor, perPage: 100000);
        $transactions = $query->items();
        $summary = $this->reportService->summary($filters, $actor);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        // Page setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        // 2. Header Judul Laporan
        $sheet->setCellValue('A1', 'BUKU KAS DIGITAL - LAPORAN KEUANGAN KAS');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));

        $fromDateStr = !empty($filters['from']) ? Carbon::parse($filters['from'])->format('d/m/Y') : '-';
        $toDateStr = !empty($filters['to']) ? Carbon::parse($filters['to'])->format('d/m/Y') : '-';
        $subTitle = "Periode: {$fromDateStr} s.d. {$toDateStr} | Dicetak oleh: {$actor->name} (" . now()->format('d/m/Y H:i') . ")";
        $sheet->setCellValue('A2', $subTitle);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true);

        // 3. Header Tabel (Row 4)
        $headings = ['No', 'Tanggal', 'Tipe', 'Kategori', 'Outlet Toko', 'Atas Nama', 'Keterangan', 'Nominal (Rp)', 'Dicatat Oleh'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headings as $index => $heading) {
            $col = $cols[$index];
            $sheet->setCellValue("{$col}4", $heading);
        }

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // 4. Populate Data Rows (Row 5+)
        $rowNumber = 5;
        foreach ($transactions as $idx => $trx) {
            $isIncome = $trx->type->value === 'income';

            $sheet->setCellValue("A{$rowNumber}", $idx + 1);
            $sheet->setCellValue("B{$rowNumber}", $trx->date->format('d/m/Y'));
            $sheet->setCellValue("C{$rowNumber}", $isIncome ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue("D{$rowNumber}", $trx->category->name ?? '-');
            $sheet->setCellValue("E{$rowNumber}", $trx->outlet->name ?? '-');
            $sheet->setCellValue("F{$rowNumber}", $trx->payer_name);
            $sheet->setCellValue("G{$rowNumber}", $trx->description ?? '-');
            $sheet->setCellValue("H{$rowNumber}", $trx->amount);
            $sheet->setCellValue("I{$rowNumber}", $trx->creator->name ?? '-');

            // Cell formatting
            $sheet->getStyle("A{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowNumber}")->getNumberFormat()->setFormatCode('#,##0');

            // Color coding amount
            $color = $isIncome ? '047857' : 'B91C1C';
            $sheet->getStyle("H{$rowNumber}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($color));

            // Thin border
            $sheet->getStyle("A{$rowNumber}:I{$rowNumber}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $rowNumber++;
        }

        // 5. Total Summary Footer
        $summaryStartRow = $rowNumber + 1;

        $sheet->setCellValue("G{$summaryStartRow}", "TOTAL PEMASUKAN");
        $sheet->setCellValue("H{$summaryStartRow}", $summary['total_income']);
        $sheet->getStyle("G{$summaryStartRow}:H{$summaryStartRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('047857'));
        $sheet->getStyle("H{$summaryStartRow}")->getNumberFormat()->setFormatCode('#,##0');

        $summaryStartRow++;
        $sheet->setCellValue("G{$summaryStartRow}", "TOTAL PENGELUARAN");
        $sheet->setCellValue("H{$summaryStartRow}", $summary['total_expense']);
        $sheet->getStyle("G{$summaryStartRow}:H{$summaryStartRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('B91C1C'));
        $sheet->getStyle("H{$summaryStartRow}")->getNumberFormat()->setFormatCode('#,##0');

        $summaryStartRow++;
        $sheet->setCellValue("G{$summaryStartRow}", "SALDO NET (KAS)");
        $sheet->setCellValue("H{$summaryStartRow}", $summary['balance']);
        $sheet->getStyle("G{$summaryStartRow}:H{$summaryStartRow}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("H{$summaryStartRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Auto-fit columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Laporan_Keuangan_" . date('Ymd_His') . ".xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
