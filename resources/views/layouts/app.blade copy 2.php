<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BensinTracker') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gas-green': '#1DB954',
                        'gas-black': '#121212',
                    }
                }
            }
        }

        // Fungsi Tab System
        function switchTab(tabId) {
            document.querySelectorAll('section.tab-content').forEach(section => {
                section.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');

            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.replace('text-gas-green', 'text-gray-400');
                item.classList.remove('font-bold');
            });
            event.currentTarget.classList.replace('text-gray-400', 'text-gas-green');
            event.currentTarget.classList.add('font-bold');
        }
    </script>
    <style>
        body { font-family: -apple-system, sans-serif; }
        .tab-content { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Sembunyikan panah di input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="bg-gray-50 text-gas-black min-h-screen pb-24">
    {{ $slot }}
</body>
</html>
