<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOutletRequest;
use App\Http\Requests\Admin\UpdateOutletRequest;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Outlet::class);

        $search = $request->query('search');

        $outlets = Outlet::withCount('transactions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.outlets.index', compact('outlets', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Outlet::class);

        return view('admin.outlets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $this->authorize('create', Outlet::class);

        Outlet::create([
            'name'      => $request->validated('name'),
            'address'   => $request->validated('address'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.outlets.index')
            ->with('success', 'Outlet baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outlet $outlet): View
    {
        $this->authorize('update', $outlet);

        return view('admin.outlets.edit', compact('outlet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        $this->authorize('update', $outlet);

        $outlet->update([
            'name'      => $request->validated('name'),
            'address'   => $request->validated('address'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.outlets.index')
            ->with('success', "Data outlet '{$outlet->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif outlet.
     */
    public function toggleStatus(Outlet $outlet): RedirectResponse
    {
        $this->authorize('update', $outlet);

        $outlet->update(['is_active' => !$outlet->is_active]);

        $statusText = $outlet->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Outlet '{$outlet->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outlet $outlet): RedirectResponse
    {
        $this->authorize('delete', $outlet);

        // Proteksi: Jika outlet memiliki transaksi, tidak boleh dihapus permanen
        if ($outlet->transactions()->exists()) {
            return redirect()
                ->back()
                ->with('error', "Outlet '{$outlet->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi. Silakan nonaktifkan outlet ini saja.");
        }

        $outlet->delete();

        return redirect()
            ->route('admin.outlets.index')
            ->with('success', "Outlet '{$outlet->name}' berhasil dihapus.");
    }
}
