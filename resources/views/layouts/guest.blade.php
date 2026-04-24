<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}
    <title>Dompetku</title>

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

        function toggleAuth(type) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const loginBtn = document.getElementById('login-btn-tab');
            const registerBtn = document.getElementById('register-btn-tab');

            if (type === 'register') {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                registerBtn.classList.add('text-gas-black', 'border-b-2', 'border-blue-600');
                registerBtn.classList.remove('text-gray-400');
                loginBtn.classList.remove('text-gas-black', 'border-b-2', 'border-blue-600');
                loginBtn.classList.add('text-gray-400');
            } else {
                registerForm.classList.add('hidden');
                loginForm.classList.remove('hidden');
                loginBtn.classList.add('text-gas-black', 'border-b-2', 'border-blue-600');
                loginBtn.classList.remove('text-gray-400');
                registerBtn.classList.remove('text-gas-black', 'border-b-2', 'border-blue-600');
                registerBtn.classList.add('text-gray-400');
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gas-black min-h-screen flex flex-col">
    {{ $slot }}
</body>
</html>
