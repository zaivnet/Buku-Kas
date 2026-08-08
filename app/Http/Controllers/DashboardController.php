<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display dashboard with summary cards, trend chart, breakdowns, and recent transactions.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $outletId = $user->isStaff() ? $user->outlet_id : $request->query('outlet_id');

        $filters = [
            'from'      => $from,
            'to'        => $to,
            'outlet_id' => $outletId,
        ];

        // 1. Summary Metrics
        $summary = $this->reportService->summary($filters, $user);

        // 2. Trend Data (Last 30 days or filtered range)
        $trend = $this->reportService->trend($filters, $user);

        // 3. Category Breakdown Data
        $incomeBreakdown = $this->reportService->breakdownByCategory($filters, 'income', $user);
        $expenseBreakdown = $this->reportService->breakdownByCategory($filters, 'expense', $user);

        // 4. Latest 10 Transactions (Filtered by outlet if specified)
        $latestTransactions = Transaction::forUser($user)
            ->when($outletId, function ($q, $outId) {
                $q->where('outlet_id', $outId);
            })
            ->with(['category', 'outlet', 'creator'])
            ->latest('date')
            ->latest('id')
            ->take(10)
            ->get();

        // 5. Options
        $outlets = $user->isStaff()
            ? Outlet::where('id', $user->outlet_id)->get()
            : Outlet::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.index', compact(
            'summary',
            'trend',
            'incomeBreakdown',
            'expenseBreakdown',
            'latestTransactions',
            'from',
            'to',
            'outletId',
            'outlets'
        ));
    }
}
