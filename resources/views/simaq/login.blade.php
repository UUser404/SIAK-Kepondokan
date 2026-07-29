<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login SIMAQ - Sistem Mutaba'ah Al-Qur'an</title>
    <!-- Memanggil Tailwind CSS bawaan Laravel Breeze -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Animasi Berputar untuk Garis Oranye */
        @keyframes spin-slow {
            100% {
                transform: rotate(360deg);
            }
        }

        .animate-border {
            animation: spin-slow 4s linear infinite;
        }
    </style>
</head>

<body class="bg-green-50 font-sans text-gray-900 antialiased min-h-screen flex flex-col justify-center items-center p-4">

    <!-- Logo / Header SIMAQ -->
    <div class="text-center mb-6 w-full max-w-sm">
        <div class="flex justify-center mb-2">
            <svg class="w-14 h-14 text-green-700 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-green-800 tracking-tight">SIMAQ</h1>
        <p class="text-green-600 mt-1 text-sm font-semibold">Sistem Informasi Mutaba'ah Al-Qur'an</p>
    </div>

    <!-- PEMBUNGKUS ANIMASI GARIS ORANYE -->
    <div class="relative w-full max-w-sm overflow-hidden rounded-2xl p-[3px] shadow-2xl">

        <!-- Elemen animasi (z-0 agar di belakang) -->
        <div class="absolute inset-[-100%] animate-border z-0"
            style="background: conic-gradient(from 0deg, transparent 60%, rgba(249, 115, 22, 0.9) 90%, transparent 100%);">
        </div>

        <!-- KOTAK FORM LOGIN (Ditambahkan z-10 agar selalu di depan animasi) -->
        <div class="relative z-10 bg-white w-full rounded-[14px] px-8 py-8 h-full flex flex-col">

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- action route('login') inilah yang menghubungkannya 100% ke database otentikasi SIAK -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email / Username Address -->
                <div>
                    <label class="block font-semibold text-sm text-gray-700" for="email">Email / Username</label>
                    <input id="email" class="block mt-2 w-full px-4 py-2 border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 rounded-lg shadow-sm transition-all text-sm" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-xs" />
                </div>

                <!-- Password -->
                <div class="mt-5">
                    <label class="block font-semibold text-sm text-gray-700" for="password">Password</label>
                    <input id="password" class="block mt-2 w-full px-4 py-2 border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 rounded-lg shadow-sm transition-all text-sm" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-xs" />
                </div>

                <!-- FITUR INGAT SAYA (REMEMBER ME) -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500 transition-colors" name="remember">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya 30 hari</span>
                    </label>
                </div>

                <!-- Aksi Bawah (Tombol Kembali & Masuk) -->
                <div class="flex items-center justify-between mt-8">
                    <a class="text-xs font-medium text-gray-500 hover:text-green-700 transition-colors focus:outline-none" href="{{ url('/') }}">
                        &larr; Kembali ke SIAK
                    </a>

                    <!-- Tombol Masuk yang Anda sediakan -->
                    <!-- Tombol Masuk (Instant Render dengan Inline CSS) -->
                    <button type="submit" style="display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; background-color: #15803d; color: #ffffff; border: none; border-radius: 0.5rem; font-weight: bold; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: background-color 0.2s ease-in-out;" onmouseover="this.style.backgroundColor='#166534'" onmouseout="this.style.backgroundColor='#15803d'">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-gray-500 font-medium">
        &copy; {{ date('Y') }} SIMAQ v1 - Terintegrasi SIAK Kepondokan
    </div>
</body>

</html>