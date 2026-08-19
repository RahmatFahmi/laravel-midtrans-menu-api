<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="text-center max-w-lg">
        <div class="relative mb-8 animate-float">
            <svg class="mx-auto w-64 h-64 text-primary" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" fill="#E0E7FF" />
                <path d="M70 110 C 70 110, 100 140, 130 110" stroke="#4F46E5" stroke-width="8" fill="none"
                    stroke-linecap="round" />
                <circle cx="75" cy="85" r="6" fill="#4F46E5" />
                <circle cx="125" cy="85" r="6" fill="#4F46E5" />
                <path d="M100 90 L 100 105" stroke="#4F46E5" stroke-width="5" stroke-linecap="round" />
                <text x="150" y="50" font-size="40" font-weight="bold" fill="#6366F1">?</text>
            </svg>
        </div>

        <h1 class="text-9xl font-extrabold text-indigo-600 mb-4">404</h1>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Waduh! Kamu Nyasar Ya?</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Halaman yang kamu cari tidak ada atau mungkin sudah pindah alamat.
            Jangan khawatir, orang hebat pun pernah tersesat.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">

            @if (Auth::check())
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Ke Login
                </a>
            @endif

        </div>
    </div>

</body>

</html>
