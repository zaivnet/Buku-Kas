<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Outlet;
use App\Services\ExcelExportService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ExcelExportService $excelExportService
    ) {}

    /**
     * Display reports page with custom filters, summary totals, and paginated transaction records.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $type = $request->query('type');
        $categoryId = $request->query('category_id');
        $outletId = $user->isStaff() ? $user->outlet_id : $request->query('outlet_id');

        $filters = [
            'from'        => $from,
            'to'          => $to,
            'type'        => $type,
            'category_id' => $categoryId,
            'outlet_id'   => $outletId,
        ];

        // 1. Total Ringkasan
        $summary = $this->reportService->summary($filters, $user);

        // 2. Daftar Transaksi Laporan (Paginated 25 per page)
        $transactions = $this->reportService->getReportTransactions($filters, $user, 25);

        // 3. Dropdown options
        $categories = Category::active()->orderBy('type')->orderBy('name')->get();
        $outlets = $user->isStaff()
            ? Outlet::where('id', $user->outlet_id)->get()
            : Outlet::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact(
            'summary',
            'transactions',
            'from',
            'to',
            'type',
            'categoryId',
            'outletId',
            'categories',
            'outlets'
        ));
    }

    /**
     * Export filtered transactions to Excel (.xlsx).
     */
    public function excel(Request $request): StreamedResponse
    {
        $user = auth()->user();

        $filters = [
            'from'        => $request->query('from', now()->startOfMonth()->toDateString()),
            'to'          => $request->query('to', now()->toDateString()),
            'type'        => $request->query('type'),
            'category_id' => $request->query('category_id'),
            'outlet_id'   => $user->isStaff() ? $user->outlet_id : $request->query('outlet_id'),
        ];

        return $this->excelExportService->export($filters, $user);
    }

    /**
     * Export filtered transactions to PDF (.pdf).
     */
    public function pdf(Request $request): mixed
    {
        $user = auth()->user();

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $type = $request->query('type');
        $categoryId = $request->query('category_id');
        $outletId = $user->isStaff() ? $user->outlet_id : $request->query('outlet_id');

        $filters = [
            'from'        => $from,
            'to'          => $to,
            'type'        => $type,
            'category_id' => $categoryId,
            'outlet_id'   => $outletId,
        ];

        // Batas wajar 2.000 record untuk proteksi memory & execution time di shared hosting
        $queryPaginator = $this->reportService->getReportTransactions($filters, $user, perPage: 2000);
        $transactions = $queryPaginator->items();

        if ($queryPaginator->total() > 2000) {
            return redirect()
                ->back()
                ->with('error', 'Jumlah data laporan terlalu besar (' . number_format($queryPaginator->total()) . ' record) untuk di-export ke PDF secara instan. Silakan persempit rentang tanggal atau gunakan export Excel.');
        }

        $summary = $this->reportService->summary($filters, $user);
        $outletName = $outletId ? Outlet::find($outletId)?->name : null;

        $pdf = Pdf::loadView('reports.pdf', compact(
            'transactions',
            'summary',
            'from',
            'to',
            'type',
            'outletName'
        ))->setPaper('a4', 'landscape');

        $filename = "Laporan_Keuangan_" . date('Ymd_His') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Display print-friendly view for browser printing.
     */
    public function print(Request $request): View
    {
        $user = auth()->user();

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $type = $request->query('type');
        $categoryId = $request->query('category_id');
        $outletId = $user->isStaff() ? $user->outlet_id : $request->query('outlet_id');

        $filters = [
            'from'        => $from,
            'to'          => $to,
            'type'        => $type,
            'category_id' => $categoryId,
            'outlet_id'   => $outletId,
        ];

        $transactions = $this->reportService->getReportTransactions($filters, $user, perPage: 1000)->items();
        $summary = $this->reportService->summary($filters, $user);
        $outletName = $outletId ? Outlet::find($outletId)?->name : null;

        return view('reports.print', compact(
            'transactions',
            'summary',
            'from',
            'to',
            'type',
            'outletName'
        ));
    }
}
