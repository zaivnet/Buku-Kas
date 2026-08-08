<x-app-layout title="Edit Outlet — {{ $outlet->name }}">
    <div class="max-w-2xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-neutral-text">Edit Outlet</h1>
                <p class="text-sm text-neutral-muted mt-1">Perbarui informasi data {{ $outlet->name }}.</p>
            </div>
            <a href="{{ route('admin.outlets.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        {{-- Card Form --}}
        <div class="card p-6">
            <form action="{{ route('admin.outlets.update', $outlet) }}" method="POST">
                @method('PUT')
                @include('admin.outlets.form')

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-neutral-border">
                    <a href="{{ route('admin.outlets.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
