<x-app-layout title="Manajemen Kategori">
    <div
        x-data="{
            createModalOpen: false,
            editModalOpen: false,
            editCategory: { id: null, name: '', type: '{{ $type }}', is_active: true, actionUrl: '' },
            openEditModal(id, name, type, isActive, updateUrl) {
                this.editCategory = { id, name, type, is_active: isActive, actionUrl: updateUrl };
                this.editModalOpen = true;
            }
        }"
    >
        {{-- Header & Button Tambah --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-neutral-text">Manajemen Kategori</h1>
                <p class="text-sm text-neutral-muted mt-1">Kelola kategori custom untuk transaksi pemasukan dan pengeluaran.</p>
            </div>
            <button
                type="button"
                x-on:click="createModalOpen = true"
                class="btn-primary flex-shrink-0 self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kategori
            </button>
        </div>

        {{-- Tab Switcher & Search Bar --}}
        <div class="card p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- Tabs --}}
                <div class="flex items-center gap-2 border-b sm:border-b-0 border-neutral-border pb-2 sm:pb-0">
                    <a
                        href="{{ route('admin.categories.index', ['type' => 'income', 'search' => $search]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 {{ $type === 'income' ? 'bg-success-100 text-success-700 font-semibold' : 'text-neutral-muted hover:bg-neutral-bg' }}"
                    >
                        <span class="w-2 h-2 rounded-full bg-success-600"></span>
                        Pemasukan
                        <span class="px-2 py-0.5 text-xs rounded-full bg-white/80 font-bold">{{ $incomeCount }}</span>
                    </a>

                    <a
                        href="{{ route('admin.categories.index', ['type' => 'expense', 'search' => $search]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 {{ $type === 'expense' ? 'bg-danger-100 text-danger-700 font-semibold' : 'text-neutral-muted hover:bg-neutral-bg' }}"
                    >
                        <span class="w-2 h-2 rounded-full bg-danger-600"></span>
                        Pengeluaran
                        <span class="px-2 py-0.5 text-xs rounded-full bg-white/80 font-bold">{{ $expenseCount }}</span>
                    </a>
                </div>

                {{-- Search Bar --}}
                <form method="GET" action="{{ route('admin.categories.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="type" value="{{ $type }}" />
                    <div class="relative flex-1 sm:w-64">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari nama kategori..."
                            class="form-input pl-10"
                        />
                        <svg class="w-5 h-5 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="btn-secondary">Cari</button>
                    @if($search)
                        <a href="{{ route('admin.categories.index', ['type' => $type]) }}" class="text-sm text-neutral-muted hover:text-neutral-text">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table List --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-bg text-neutral-muted text-xs uppercase tracking-wider border-b border-neutral-border">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Nama Kategori</th>
                            <th class="px-6 py-3 font-semibold">Tipe</th>
                            <th class="px-6 py-3 font-semibold">Total Transaksi</th>
                            <th class="px-6 py-3 font-semibold text-center">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-border bg-white">
                        @forelse($categories as $category)
                            <tr class="hover:bg-neutral-bg/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-neutral-text">
                                    {{ $category->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($category->type->value === 'income')
                                        <x-badge type="income">Pemasukan</x-badge>
                                    @else
                                        <x-badge type="expense">Pengeluaran</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-text">
                                    <span class="font-semibold">{{ number_format($category->transactions_count) }}</span> transaksi
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($category->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="danger">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        {{-- Toggle Status Button --}}
                                        <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="text-xs px-2.5 py-1 rounded border border-neutral-border hover:bg-neutral-bg text-neutral-muted hover:text-neutral-text transition-colors"
                                                title="{{ $category->is_active ? 'Nonaktifkan Kategori' : 'Aktifkan Kategori' }}"
                                            >
                                                {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        {{-- Edit Button (Trigger Modal) --}}
                                        <button
                                            type="button"
                                            x-on:click="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->type->value }}', {{ $category->is_active ? 'true' : 'false' }}, '{{ route('admin.categories.update', $category) }}')"
                                            class="p-1.5 text-neutral-muted hover:text-primary rounded hover:bg-neutral-bg transition-colors"
                                            title="Edit Kategori"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button (Disabled if category has transactions) --}}
                                        @if($category->transactions_count > 0)
                                            <button
                                                type="button"
                                                disabled
                                                class="p-1.5 text-neutral-300 cursor-not-allowed rounded"
                                                title="Kategori ini sudah memiliki riwayat transaksi dan tidak bisa dihapus. Gunakan tombol 'Nonaktifkan' jika ingin menghentikan penggunaan."
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @else
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'{{ $category->name }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1.5 text-neutral-muted hover:text-danger rounded hover:bg-neutral-bg transition-colors"
                                                    title="Hapus Kategori"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Belum ada kategori {{ $type === 'income' ? 'pemasukan' : 'pengeluaran' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($categories->hasPages())
                <div class="p-4 border-t border-neutral-border bg-neutral-bg/30">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>

        {{-- ======================== MODAL TAMBAH KATEGORI ======================== --}}
        <div
            x-show="createModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            style="display: none;"
        >
            <div x-show="createModalOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="createModalOpen = false"></div>

            <div x-show="createModalOpen" class="bg-white rounded-xl overflow-hidden shadow-xl border border-neutral-border w-full sm:max-w-md z-10 p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-neutral-border">
                    <h3 class="text-base font-bold text-neutral-text">Tambah Kategori Baru</h3>
                    <button type="button" x-on:click="createModalOpen = false" class="text-neutral-400 hover:text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <x-form.input
                            label="Nama Kategori"
                            name="name"
                            placeholder="Contoh: Setoran Outlet, Gaji, dll."
                            required
                        />

                        <x-form.select
                            label="Tipe Kategori"
                            name="type"
                            required
                        >
                            <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Pemasukan (Income)</option>
                            <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Pengeluaran (Expense)</option>
                        </x-form.select>

                        <div>
                            <label for="create_is_active" class="inline-flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    id="create_is_active"
                                    value="1"
                                    checked
                                    class="rounded border-neutral-border text-primary focus:ring-primary"
                                />
                                <span class="text-sm font-medium text-neutral-text">Kategori Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-neutral-border">
                        <button type="button" x-on:click="createModalOpen = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================== MODAL EDIT KATEGORI ======================== --}}
        <div
            x-show="editModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            style="display: none;"
        >
            <div x-show="editModalOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="editModalOpen = false"></div>

            <div x-show="editModalOpen" class="bg-white rounded-xl overflow-hidden shadow-xl border border-neutral-border w-full sm:max-w-md z-10 p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-neutral-border">
                    <h3 class="text-base font-bold text-neutral-text">Edit Kategori</h3>
                    <button type="button" x-on:click="editModalOpen = false" class="text-neutral-400 hover:text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="editCategory.actionUrl" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="form-label">Nama Kategori <span class="text-danger-600">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="edit_name"
                                x-model="editCategory.name"
                                required
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label for="edit_type" class="form-label">Tipe Kategori <span class="text-danger-600">*</span></label>
                            <select name="type" id="edit_type" x-model="editCategory.type" class="form-input" required>
                                <option value="income">Pemasukan (Income)</option>
                                <option value="expense">Pengeluaran (Expense)</option>
                            </select>
                        </div>

                        <div>
                            <label for="edit_is_active" class="inline-flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    id="edit_is_active"
                                    value="1"
                                    x-model="editCategory.is_active"
                                    class="rounded border-neutral-border text-primary focus:ring-primary"
                                />
                                <span class="text-sm font-medium text-neutral-text">Kategori Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-neutral-border">
                        <button type="button" x-on:click="editModalOpen = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
