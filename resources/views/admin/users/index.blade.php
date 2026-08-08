<x-app-layout title="Manajemen Pengguna">
    {{-- Header & Button Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Manajemen Pengguna</h1>
            <p class="text-sm text-neutral-muted mt-1">Kelola akun pengguna, peranan (role), dan penetapan outlet.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary flex-shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            {{-- Search --}}
            <div class="sm:col-span-2 relative">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama atau email pengguna..."
                    class="form-input pl-10"
                />
                <svg class="w-5 h-5 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Filter Role --}}
            <div>
                <select name="role" class="form-input">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->value }}" {{ $role === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Outlet --}}
            <div class="flex items-center gap-2">
                <select name="outlet_id" class="form-input flex-1">
                    <option value="">Semua Outlet</option>
                    @foreach($outlets as $o)
                        <option value="{{ $o->id }}" {{ $outletId == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn-secondary">Cari</button>
            </div>
        </form>
    </div>

    {{-- Table List --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-bg text-neutral-muted text-xs uppercase tracking-wider border-b border-neutral-border">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Pengguna</th>
                        <th class="px-6 py-3 font-semibold">Role</th>
                        <th class="px-6 py-3 font-semibold">Outlet Assigned</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-border bg-white">
                    @forelse($users as $userItem)
                        <tr class="hover:bg-neutral-bg/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-50 text-primary flex items-center justify-center font-bold text-sm">
                                        {{ substr($userItem->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-neutral-text">{{ $userItem->name }}</p>
                                        <p class="text-xs text-neutral-muted">{{ $userItem->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($userItem->isAdmin())
                                    <x-badge type="primary">Admin</x-badge>
                                @elseif($userItem->isStaff())
                                    <x-badge type="warning">Staff</x-badge>
                                @else
                                    <x-badge type="default">Viewer</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-muted">
                                @if($userItem->outlet)
                                    <span class="font-medium text-neutral-text">{{ $userItem->outlet->name }}</span>
                                @else
                                    <span class="text-neutral-400 font-normal">Semua Outlet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($userItem->is_active)
                                    <x-badge type="success">Aktif</x-badge>
                                @else
                                    <x-badge type="danger">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Toggle Status Button --}}
                                    @if(auth()->id() !== $userItem->id)
                                        <form action="{{ route('admin.users.toggle-status', $userItem) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="text-xs px-2.5 py-1 rounded border border-neutral-border hover:bg-neutral-bg text-neutral-muted hover:text-neutral-text transition-colors"
                                                title="{{ $userItem->is_active ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna' }}"
                                            >
                                                {{ $userItem->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Edit Button --}}
                                    <a
                                        href="{{ route('admin.users.edit', $userItem) }}"
                                        class="p-1.5 text-neutral-muted hover:text-primary rounded hover:bg-neutral-bg transition-colors"
                                        title="Edit Pengguna"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Delete Button --}}
                                    @if(auth()->id() !== $userItem->id)
                                        <form action="{{ route('admin.users.destroy', $userItem) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna \'{{ $userItem->name }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-neutral-muted hover:text-danger rounded hover:bg-neutral-bg transition-colors"
                                                title="Hapus Pengguna"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-neutral-muted">
                                <svg class="w-12 h-12 mx-auto text-neutral-border mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="p-4 border-t border-neutral-border bg-neutral-bg/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
