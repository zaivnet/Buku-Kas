<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Server | Buku Kas Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center p-6 font-sans">

    <div class="max-w-md w-full text-center bg-white p-8 rounded-2xl border border-gray-200 shadow-xl">
        <div class="w-20 h-20 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <span class="text-xs font-bold uppercase tracking-wider text-red-600 bg-red-50 px-3 py-1 rounded-full">Error 500</span>

        <h1 class="text-2xl font-extrabold text-gray-900 mt-3 mb-2">Terjadi Kesalahan Server</h1>

        <p class="text-sm text-gray-500 leading-relaxed mb-8">
            Maaf, sistem sedang mengalami kendala teknis internal. Tim teknis sedang memproses masalah ini. Silakan muat ulang halaman beberapa saat lagi.
        </p>

        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-900 hover:bg-blue-800 text-white font-medium text-sm rounded-xl transition-colors shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Muat Ulang Dashboard
        </a>
    </div>

</body>
</html>
