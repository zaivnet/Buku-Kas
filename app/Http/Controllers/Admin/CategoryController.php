<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $type = $request->query('type', TransactionTypeEnum::INCOME->value);
        if (!in_array($type, [TransactionTypeEnum::INCOME->value, TransactionTypeEnum::EXPENSE->value], true)) {
            $type = TransactionTypeEnum::INCOME->value;
        }

        $search = $request->query('search');

        $categories = Category::with('creator')
            ->withCount('transactions')
            ->ofType($type)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $incomeCount = Category::ofType(TransactionTypeEnum::INCOME)->count();
        $expenseCount = Category::ofType(TransactionTypeEnum::EXPENSE)->count();

        return view('admin.categories.index', compact('categories', 'type', 'search', 'incomeCount', 'expenseCount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        Category::create([
            'name'       => $request->validated('name'),
            'type'       => $request->validated('type'),
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.categories.index', ['type' => $request->validated('type')])
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update([
            'name'      => $request->validated('name'),
            'type'      => $request->validated('type'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.categories.index', ['type' => $category->type->value])
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif kategori.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update(['is_active' => !$category->is_active]);

        $statusText = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Kategori '{$category->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        // Proteksi: Kategori yang sudah memiliki transaksi tidak bisa dihapus permanen
        if ($category->transactions()->exists()) {
            return redirect()
                ->back()
                ->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi. Silakan nonaktifkan kategori ini saja.");
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index', ['type' => $category->type->value])
            ->with('success', "Kategori '{$category->name}' berhasil dihapus.");
    }
}
