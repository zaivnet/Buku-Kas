<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? 'Aplikasi manajemen keuangan buku kas digital.' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- PWA & Mobile Native Meta -->
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-neutral-bg font-sans antialiased">

<div class="min-h-full" x-data="{ sidebarOpen: false }">

    {{-- ======================== TOPBAR ======================== --}}
    <nav class="fixed top-0 left-0 right-0 z-30 h-16 bg-primary border-b border-primary-900 flex items-center justify-between px-4 lg:px-6">
        {{-- Kiri: Hamburger (mobile) + Logo --}}
        <div class="flex items-center gap-3">
            {{-- Hamburger (mobile only) --}}
            <button
                type="button"
                class="lg:hidden text-white/80 hover:text-white transition-colors"
                x-on:click="sidebarOpen = !sidebarOpen"
                aria-label="Buka menu navigasi"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Logo & App Name --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-white font-semibold text-sm hidden sm:block">{{ config('app.name') }}</span>
            </a>
        </div>

        {{-- Tengah: Nama outlet aktif (tampil hanya jika ada) --}}
        <div class="hidden md:block">
            @if(auth()->user()?->outlet)
                <span class="text-white/70 text-sm">
                    <span class="text-white/50">Outlet:</span>
                    {{ auth()->user()->outlet->name }}
                </span>
            @endif
        </div>

        {{-- Kanan: User dropdown --}}
        <div class="flex items-center gap-2" x-data="{ userMenuOpen: false }">
            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 text-white/80 hover:text-white transition-colors"
                    x-on:click="userMenuOpen = !userMenuOpen"
                    x-on:click.away="userMenuOpen = false"
                    aria-label="Menu pengguna"
                    aria-haspopup="true"
                    :aria-expanded="userMenuOpen"
                >
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-sm font-medium text-white">
                        {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
                    </div>
                    <span class="hidden md:block text-sm font-medium">{{ auth()->user()?->name }}</span>
                    <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown user --}}
                <div
                    x-show="userMenuOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-neutral-border py-1 z-50"
                    role="menu"
                >
                    <div class="px-4 py-2 border-b border-neutral-border">
                        <p class="text-sm font-medium text-neutral-text truncate">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-neutral-muted truncate">{{ auth()->user()?->email }}</p>
                        <span class="inline-flex mt-1 px-2 py-0.5 bg-primary-50 text-primary text-xs rounded-full font-medium">
                            {{ auth()->user()?->role?->label() ?? '' }}
                        </span>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-neutral-text hover:bg-neutral-bg" role="menuitem">
                        <svg class="w-4 h-4 text-neutral-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <button type="button" x-on:click="$dispatch('open-about-modal')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-neutral-text hover:bg-neutral-bg" role="menuitem">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tentang Aplikasi
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger hover:bg-danger-50" role="menuitem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- ======================== SIDEBAR + CONTENT ======================== --}}
    <div class="flex h-full pt-16">

        {{-- Overlay mobile --}}
        <div
            x-show="sidebarOpen"
            x-on:click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            aria-hidden="true"
        ></div>

        {{-- SIDEBAR --}}
        <aside
            class="fixed left-0 top-16 bottom-0 z-20 w-64 bg-white border-r border-neutral-border flex flex-col overflow-y-auto transform transition-transform duration-200 ease-in-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <nav class="flex-1 p-4 space-y-1" aria-label="Navigasi utama">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                {{-- Pemasukan --}}
                <a href="{{ route('transactions.income') }}"
                   class="sidebar-link {{ request()->routeIs('transactions.income') || (request()->routeIs('transactions.*') && request()->query('type') === 'income') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pemasukan
                </a>

                {{-- Pengeluaran --}}
                <a href="{{ route('transactions.expense') }}"
                   class="sidebar-link {{ request()->routeIs('transactions.expense') || (request()->routeIs('transactions.*') && request()->query('type') === 'expense') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Pengeluaran
                </a>

                {{-- Laporan --}}
                <a href="{{ route('reports.index') }}"
                   class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan
                </a>

                {{-- Divider admin --}}
                @can('admin-only')
                <div class="my-3 border-t border-neutral-border"></div>
                <p class="px-3 mb-1 text-xs font-semibold text-neutral-muted uppercase tracking-wider">Administrasi</p>

                {{-- Kategori --}}
                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori
                </a>

                {{-- Outlet --}}
                <a href="{{ route('admin.outlets.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.outlets.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Outlet
                </a>

                {{-- User --}}
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Pengguna
                </a>
                @endcan
            </nav>

            {{-- Footer sidebar --}}
            <div class="p-4 border-t border-neutral-border text-center space-y-1">
                <button type="button" x-on:click="$dispatch('open-about-modal')" class="text-xs text-primary font-medium hover:underline inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tentang Aplikasi
                </button>
                <p class="text-[11px] text-neutral-muted">{{ config('app.name') }} &copy; {{ date('Y') }}</p>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 lg:ml-64 min-h-screen p-4 lg:p-6 pb-24 sm:pb-6 overflow-x-hidden">

            {{-- Flash messages --}}
            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 4000)"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="mb-4 flex items-center gap-3 p-4 bg-success-50 border border-success-100 text-success-700 rounded-lg text-sm"
                    role="alert"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                    <button x-on:click="show = false" class="ml-auto text-success-600 hover:text-success-800" aria-label="Tutup notifikasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    class="mb-4 flex items-center gap-3 p-4 bg-danger-50 border border-danger-100 text-danger-700 rounded-lg text-sm"
                    role="alert"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                    <button x-on:click="show = false" class="ml-auto text-danger-600 hover:text-danger-800" aria-label="Tutup notifikasi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

{{-- Global Toast Notifications --}}
<x-toast />

{{-- Mobile Native Bottom Navigation Bar --}}
<x-bottom-nav />

{{-- About Application Modal --}}
<x-about-modal />

@stack('scripts')
</body>
</html>
