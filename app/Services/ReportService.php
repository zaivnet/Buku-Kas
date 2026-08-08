<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get summary totals (income, expense, balance).
     */
    public function summary(array $filters, User $actor): array
    {
        $query = $this->buildFilteredQuery($filters, $actor);

        $totals = $query->selectRaw("
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        ")->first();

        $totalIncome = (int) ($totals->total_income ?? 0);
        $totalExpense = (int) ($totals->total_expense ?? 0);
        $balance = $totalIncome - $totalExpense;

        return [
            'total_income'  => $totalIncome,
            'total_expense' => $totalExpense,
            'balance'       => $balance,
        ];
    }

    /**
     * Get trend chart data (labels, income array, expense array).
     * Grouped by day if range <= 60 days, otherwise grouped by month.
     */
    public function trend(array $filters, User $actor): array
    {
        $fromDate = !empty($filters['from']) ? Carbon::parse($filters['from']) : now()->subDays(29)->startOfDay();
        $toDate = !empty($filters['to']) ? Carbon::parse($filters['to']) : now()->endOfDay();

        $diffInDays = $fromDate->diffInDays($toDate);
        $groupByMonth = $diffInDays > 60;

        $filters['from'] = $fromDate->toDateString();
        $filters['to'] = $toDate->toDateString();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            $dateFormatExpr = $groupByMonth ? "strftime('%Y-%m', date)" : "strftime('%Y-%m-%d', date)";
        } else {
            $dateFormatExpr = $groupByMonth ? "DATE_FORMAT(date, '%Y-%m')" : "DATE_FORMAT(date, '%Y-%m-%d')";
        }

        $query = $this->buildFilteredQuery($filters, $actor);

        $results = $query->selectRaw("
            {$dateFormatExpr} as period,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_sum,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_sum
        ")
        ->groupByRaw("{$dateFormatExpr}")
        ->orderByRaw("{$dateFormatExpr} ASC")
        ->get();

        $periodMap = [];
        foreach ($results as $row) {
            $periodMap[$row->period] = [
                'income'  => (int) $row->income_sum,
                'expense' => (int) $row->expense_sum,
            ];
        }

        // Generate full period sequence so there are no empty gaps in chart
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        if ($groupByMonth) {
            $cursor = $fromDate->copy()->startOfMonth();
            $end = $toDate->copy()->startOfMonth();

            while ($cursor->lessThanOrEqualTo($end)) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $incomeData[] = $periodMap[$key]['income'] ?? 0;
                $expenseData[] = $periodMap[$key]['expense'] ?? 0;
                $cursor->addMonth();
            }
        } else {
            $cursor = $fromDate->copy();
            while ($cursor->lessThanOrEqualTo($toDate)) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d M');
                $incomeData[] = $periodMap[$key]['income'] ?? 0;
                $expenseData[] = $periodMap[$key]['expense'] ?? 0;
                $cursor->addDay();
            }
        }

        return [
            'labels'  => $labels,
            'income'  => $incomeData,
            'expense' => $expenseData,
        ];
    }

    /**
     * Get breakdown total by category for chart.
     */
    public function breakdownByCategory(array $filters, string $type, User $actor): array
    {
        $filters['type'] = $type;
        $query = $this->buildFilteredQuery($filters, $actor);

        $results = $query->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw("categories.name as category_name, SUM(transactions.amount) as total_amount")
            ->groupBy('categories.id', 'categories.name')
            ->orderByRaw("SUM(transactions.amount) DESC")
            ->get();

        $labels = [];
        $values = [];

        foreach ($results as $row) {
            $labels[] = $row->category_name;
            $values[] = (int) $row->total_amount;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get paginated transactions for reports listing.
     */
    public function getReportTransactions(array $filters, User $actor, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters, $actor);

        return $query->with(['category', 'outlet', 'creator'])
            ->latest('date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Build base query with multi-tenancy outlet scoping & filters.
     */
    protected function buildFilteredQuery(array $filters, User $actor)
    {
        $query = Transaction::query();

        // 1. Scoping Outlet untuk Staff
        if ($actor->isStaff()) {
            $query->where('transactions.outlet_id', $actor->outlet_id);
        } elseif (!empty($filters['outlet_id'])) {
            $query->where('transactions.outlet_id', $filters['outlet_id']);
        }

        // 2. Filter Rentang Tanggal
        if (!empty($filters['from'])) {
            $query->whereDate('transactions.date', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('transactions.date', '<=', $filters['to']);
        }

        // 3. Filter Tipe Transaksi
        if (!empty($filters['type'])) {
            $query->where('transactions.type', $filters['type']);
        }

        // 4. Filter Kategori
        if (!empty($filters['category_id'])) {
            $query->where('transactions.category_id', $filters['category_id']);
        }

        return $query;
    }
}
