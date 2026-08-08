<x-guest-layout title="Masuk">
    {{-- Status sesi (misal: link reset password sudah dikirim) --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-lg font-semibold text-neutral-text mb-6 text-center">Masuk ke Akun Anda</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="form-label">Alamat Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="form-input @error('email') border-danger focus:border-danger focus:ring-danger @enderror"
                placeholder="nama@contoh.com"
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <label for="password" class="form-label">Kata Sandi</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-input @error('password') border-danger focus:border-danger focus:ring-danger @enderror"
                placeholder="••••••••"
            />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ingat saya --}}
        <div class="mt-4">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-neutral-border text-primary shadow-sm focus:ring-primary"
                >
                <span class="text-sm text-neutral-muted">Ingat saya</span>
            </label>
        </div>

        {{-- Tombol & Lupa Password --}}
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm text-primary hover:text-primary-700 underline"
                >
                    Lupa kata sandi?
                </a>
            @endif

            <button
                type="submit"
                id="btn-login"
                class="btn-primary ml-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Masuk
            </button>
        </div>
    </form>
</x-guest-layout>
