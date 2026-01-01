<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Tidak Tersedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Toko Tidak Tersedia
            </h1>

            <p class="text-gray-600 mb-4">
                Maaf, toko ini sedang tidak aktif untuk sementara waktu.
            </p>

            @if($reason)
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>Alasan:</strong> {{ $reason }}
                </p>
            </div>
            @endif

            <p class="text-sm text-gray-500">
                Silakan hubungi pemilik toko atau coba lagi nanti.
            </p>

            <div class="mt-6">
                <a href="/" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700 transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            Powered by Multi-Tenant E-Commerce
        </p>
    </div>
</body>
</html>
