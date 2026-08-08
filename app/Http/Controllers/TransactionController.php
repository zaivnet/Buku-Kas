<?php

namespace App\Http\Controllers;

use App\Enums\TransactionTypeEnum;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * Alias route khusus Pemasukan.
     */
    public function income(Request $request): View
    {
        $request->merge(['type' => TransactionTypeEnum::INCOME->value]);
        return $this->index($request);
    }

    /**
     * Alias route khusus Pengeluaran.
     */
    public function expense(Request $request): View
    {
        $request->merge(['type' => TransactionTypeEnum::EXPENSE->value]);
        return $this->index($request);
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Transaction::class);

        $user = auth()->user();
        $type = $request->query('type', TransactionTypeEnum::INCOME->value);

        if (!in_array($type, [TransactionTypeEnum::INCOME->value, TransactionTypeEnum::EXPENSE->value], true)) {
            $type = TransactionTypeEnum::INCOME->value;
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $categoryId = $request->query('category_id');
        $outletId = $user->isStaff() ? $user->outlet_id : $request->query('outlet_id');
        $search = $request->query('search');

        // Query transaksi dengan forUser scope (outlet scoping otomatis untuk Staff)
        $query = Transaction::forUser($user)
            ->with(['category', 'outlet', 'creator', 'updater'])
            ->where('type', $type)
            ->betweenDates($from, $to)
            ->when($categoryId, function ($q, $catId) {
                $q->where('category_id', $catId);
            })
            ->when($outletId, function ($q, $outId) {
                $q->where('outlet_id', $outId);
            })
            ->when($search, function ($q, $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('payer_name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            });

        // Running total sum untuk data terfilter saat ini
        $totalAmount = (clone $query)->sum('amount');

        $transactions = $query->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Options dropdown
        $categories = Category::active()->ofType($type)->orderBy('name')->get();
        $outlets = $user->isStaff()
            ? Outlet::where('id', $user->outlet_id)->get()
            : Outlet::where('is_active', true)->orderBy('name')->get();

        return view('transactions.index', compact(
            'transactions',
            'type',
            'from',
            'to',
            'categoryId',
            'outletId',
            'search',
            'totalAmount',
            'categories',
            'outlets'
        ));
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Transaction::class);

        $user = auth()->user();
        $type = $request->query('type', TransactionTypeEnum::INCOME->value);

        if (!in_array($type, [TransactionTypeEnum::INCOME->value, TransactionTypeEnum::EXPENSE->value], true)) {
            $type = TransactionTypeEnum::INCOME->value;
        }

        $categories = Category::active()->ofType($type)->orderBy('name')->get();
        $outlets = $user->isStaff()
            ? Outlet::where('id', $user->outlet_id)->get()
            : Outlet::where('is_active', true)->orderBy('name')->get();

        return view('transactions.create', compact('type', 'categories', 'outlets'));
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $transaction = $this->transactionService->create(
            $request->validated(),
            auth()->user(),
            $request->file('proof_image')
        );

        $typeName = $transaction->type->value === 'income' ? 'Pemasukan' : 'Pengeluaran';
        $redirectRoute = $transaction->type->value === 'income' ? 'transactions.income' : 'transactions.expense';

        return redirect()
            ->route($redirectRoute)
            ->with('success', "Transaksi {$typeName} Rp " . number_format($transaction->amount, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Display the specified transaction detail.
     */
    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['category', 'outlet', 'creator', 'updater']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $user = auth()->user();
        $type = $transaction->type->value;

        // Populate kategori aktif + kategori transaksi saat ini (jika nonaktif)
        $categories = Category::ofType($type)->where(function ($q) use ($transaction) {
            $q->where('is_active', true)->orWhere('id', $transaction->category_id);
        })->orderBy('name')->get();

        $outlets = $user->isStaff()
            ? Outlet::where('id', $user->outlet_id)->get()
            : Outlet::where('is_active', true)->orWhere('id', $transaction->outlet_id)->orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'type', 'categories', 'outlets'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $updatedTransaction = $this->transactionService->update(
            $transaction,
            $request->validated(),
            auth()->user(),
            $request->file('proof_image'),
            $request->boolean('remove_proof')
        );

        $redirectRoute = $updatedTransaction->type->value === 'income' ? 'transactions.income' : 'transactions.expense';

        return redirect()
            ->route($redirectRoute)
            ->with('success', "Transaksi berhasil diperbarui.");
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $type = $transaction->type->value;
        $this->transactionService->delete($transaction, auth()->user());

        $redirectRoute = $type === 'income' ? 'transactions.income' : 'transactions.expense';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
