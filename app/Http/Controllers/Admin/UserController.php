<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = $request->query('search');
        $role = $request->query('role');
        $outletId = $request->query('outlet_id');

        $users = User::with('outlet')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($outletId, function ($query, $outletId) {
                $query->where('outlet_id', $outletId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        $roles = RoleEnum::cases();

        return view('admin.users.index', compact('users', 'outlets', 'roles', 'search', 'role', 'outletId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();
        $roles = RoleEnum::cases();

        return view('admin.users.create', compact('outlets', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        User::create([
            'name'      => $request->validated('name'),
            'email'     => $request->validated('email'),
            'password'  => Hash::make($request->validated('password')),
            'role'      => $request->validated('role'),
            'outlet_id' => $request->validated('role') === RoleEnum::STAFF->value ? $request->validated('outlet_id') : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $outlets = Outlet::where('is_active', true)->orderBy('name')->get();

        // Jika user yang diedit saat ini terikat outlet yang dinonaktifkan, masukkan juga ke opsi dropdown agar tidak hilang
        if ($user->outlet_id && !$outlets->contains('id', $user->outlet_id)) {
            $userOutlet = Outlet::find($user->outlet_id);
            if ($userOutlet) {
                $outlets->push($userOutlet);
            }
        }

        $roles = RoleEnum::cases();

        return view('admin.users.edit', compact('user', 'outlets', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Proteksi: Admin tidak boleh menonaktifkan akunnya sendiri
        if (auth()->id() === $user->id && !$request->boolean('is_active')) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $data = [
            'name'      => $request->validated('name'),
            'email'     => $request->validated('email'),
            'role'      => $request->validated('role'),
            'outlet_id' => $request->validated('role') === RoleEnum::STAFF->value ? $request->validated('outlet_id') : null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Proteksi: Admin tidak boleh menonaktifkan akunnya sendiri
        if (auth()->id() === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Pengguna '{$user->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Proteksi: Admin tidak boleh menghapus akunnya sendiri
        if (auth()->id() === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Pengguna '{$user->name}' berhasil dihapus.");
    }
}
