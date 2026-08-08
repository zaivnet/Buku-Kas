@csrf

<div class="space-y-4" x-data="{ role: '{{ old('role', is_object($user->role ?? null) ? $user->role->value : ($user->role ?? 'staff')) }}' }">
    {{-- Nama Pengguna --}}
    <x-form.input
        label="Nama Pengguna"
        name="name"
        :value="$user->name ?? ''"
        placeholder="Contoh: Budi Santoso"
        required
    />

    {{-- Alamat Email --}}
    <x-form.input
        type="email"
        label="Alamat Email"
        name="email"
        :value="$user->email ?? ''"
        placeholder="nama@contoh.com"
        required
    />

    {{-- Kata Sandi --}}
    <x-form.input
        type="password"
        label="Kata Sandi"
        name="password"
        placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin mengubah kata sandi' : 'Minimal 8 karakter' }}"
        :required="!isset($user)"
        :hint="isset($user) ? 'Isi hanya jika ingin memperbarui kata sandi pengguna.' : null"
    />

    {{-- Role / Peranan --}}
    <x-form.select
        label="Peranan (Role)"
        name="role"
        required
        x-model="role"
    >
        @foreach($roles as $roleEnum)
            <option value="{{ $roleEnum->value }}" {{ old('role', is_object($user->role ?? null) ? $user->role->value : ($user->role ?? 'staff')) === $roleEnum->value ? 'selected' : '' }}>
                {{ $roleEnum->label() }}
            </option>
        @endforeach
    </x-form.select>

    {{-- Outlet (Hanya muncul jika role = staff) --}}
    <div x-show="role === 'staff'" x-transition class="pt-1">
        <x-form.select
            label="Tugaskan di Outlet"
            name="outlet_id"
            placeholder="-- Pilih Outlet --"
            :value="$user->outlet_id ?? ''"
            :required="false"
            hint="Pengguna dengan role Staff wajib ditugaskan pada 1 outlet."
        >
            @foreach($outlets as $outletOption)
                <option value="{{ $outletOption->id }}" {{ old('outlet_id', $user->outlet_id ?? '') == $outletOption->id ? 'selected' : '' }}>
                    {{ $outletOption->name }} {{ !$outletOption->is_active ? '(Nonaktif)' : '' }}
                </option>
            @endforeach
        </x-form.select>
    </div>

    {{-- Status Aktif --}}
    <div class="pt-2">
        <label for="is_active" class="inline-flex items-center gap-2 cursor-pointer">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                class="rounded border-neutral-border text-primary focus:ring-primary"
                {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
            />
            <span class="text-sm font-medium text-neutral-text">Akun Aktif</span>
        </label>
        <p class="text-xs text-neutral-muted mt-0.5">Pengguna nonaktif tidak akan bisa masuk ke dalam sistem.</p>
    </div>
</div>
