@csrf

<div class="space-y-4">
    <x-form.input
        label="Nama Outlet"
        name="name"
        :value="$outlet->name ?? ''"
        placeholder="Contoh: Outlet 1 - Kalasan"
        required
    />

    <x-form.textarea
        label="Alamat Outlet"
        name="address"
        :value="$outlet->address ?? ''"
        placeholder="Alamat lengkap lokasi outlet (opsional)..."
        rows="3"
    />

    <div class="pt-2">
        <label for="is_active" class="inline-flex items-center gap-2 cursor-pointer">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                class="rounded border-neutral-border text-primary focus:ring-primary"
                {{ old('is_active', $outlet->is_active ?? true) ? 'checked' : '' }}
            />
            <span class="text-sm font-medium text-neutral-text">Outlet Aktif</span>
        </label>
        <p class="text-xs text-neutral-muted mt-0.5">Outlet nonaktif tidak akan muncul di opsi pencatatan transaksi baru.</p>
    </div>
</div>
