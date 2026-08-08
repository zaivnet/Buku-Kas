<x-app-layout title="Manajemen Outlet">
    {{-- Header & Button Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Manajemen Outlet</h1>
            <p class="text-sm text-neutral-muted mt-1">Kelola daftar toko/outlet tempat transaksi keuangan berlangsung.</p>
        </div>
        <a href="{{ route('admin.outlets.create') }}" class="btn-primary flex-shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Outlet
        </a>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('admin.outlets.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama outlet atau alamat..."
                    class="form-input pl-10"
                />
                <svg class="w-5 h-5 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="btn-secondary">Cari</button>
            @if($search)
                <a href="{{ route('admin.outlets.index') }}" class="text-sm text-neutral-muted hover:text-neutral-text">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table List --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-bg text-neutral-muted text-xs uppercase tracking-wider border-b border-neutral-border">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Nama Outlet</th>
                        <th class="px-6 py-3 font-semibold">Alamat</th>
                        <th class="px-6 py-3 font-semibold">Total Transaksi</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-border bg-white">
                    @forelse($outlets as $outlet)
                        <tr class="hover:bg-neutral-bg/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-neutral-text">
                                {{ $outlet->name }}
                            </td>
                            <td class="px-6 py-4 text-neutral-muted">
                                {{ $outlet->address ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-neutral-text">
                                <span class="font-semibold">{{ number_format($outlet->transactions_count) }}</span> transaksi
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($outlet->is_active)
                                    <x-badge type="success">Aktif</x-badge>
                                @else
                                    <x-badge type="danger">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Toggle Status Button --}}
                                    <form action="{{ route('admin.outlets.toggle-status', $outlet) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="text-xs px-2.5 py-1 rounded border border-neutral-border hover:bg-neutral-bg text-neutral-muted hover:text-neutral-text transition-colors"
                                            title="{{ $outlet->is_active ? 'Nonaktifkan Outlet' : 'Aktifkan Outlet' }}"
                                        >
                                            {{ $outlet->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Edit Button --}}
                                    <a
                                        href="{{ route('admin.outlets.edit', $outlet) }}"
                                        class="p-1.5 text-neutral-muted hover:text-primary rounded hover:bg-neutral-bg transition-colors"
                                        title="Edit Outlet"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('admin.outlets.destroy', $outlet) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus outlet \'{{ $outlet->name }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-neutral-muted hover:text-danger rounded hover:bg-neutral-bg transition-colors"
                                            title="Hapus Outlet"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-neutral-muted">
                                <svg class="w-12 h-12 mx-auto text-neutral-border mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Belum ada data outlet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($outlets->hasPages())
            <div class="p-4 border-t border-neutral-border bg-neutral-bg/30">
                {{ $outlets->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
