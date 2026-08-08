<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak | {{ config('app.name') }}</title>

    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-neutral-bg font-sans antialiased flex items-center justify-center p-4">

<div class="max-w-md w-full text-center">
    {{-- Shield Error Icon --}}
    <div class="inline-flex items-center justify-center w-20 h-20 bg-danger-50 text-danger rounded-3xl mb-6 shadow-sm">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>

    {{-- Title & Message --}}
    <h1 class="text-4xl font-extrabold text-neutral-text tracking-tight mb-2">403</h1>
    <h2 class="text-xl font-bold text-neutral-text mb-3">Akses Ditolak</h2>
    <p class="text-sm text-neutral-muted mb-8 leading-relaxed">
        {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.' }}
    </p>

    {{-- Action Button --}}
    <div class="flex items-center justify-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    {{-- App Footer --}}
    <p class="mt-12 text-xs text-neutral-muted">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Hak cipta dilindungi.
    </p>
</div>

</body>
</html>
