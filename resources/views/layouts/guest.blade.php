<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="Masuk ke aplikasi Buku Kas Digital untuk mengelola keuangan Anda.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-neutral-bg font-sans antialiased">

<div class="min-h-full flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">

    {{-- Logo & Nama Aplikasi --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-2xl shadow-lg mb-4">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-neutral-text">{{ config('app.name') }}</h1>
        <p class="mt-1 text-sm text-neutral-muted">Sistem manajemen keuangan multi-outlet</p>
    </div>

    {{-- Card konten --}}
    <div class="w-full max-w-md">
        <div class="card p-8">
            {{ $slot }}
        </div>
    </div>

    {{-- Footer --}}
    <p class="mt-8 text-xs text-neutral-muted">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Hak cipta dilindungi.
    </p>
</div>

</body>
</html>
