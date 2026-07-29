<x-guest-layout>
    <div class="mb-10">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Selamat datang</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Masuk dengan akun Anda untuk melanjutkan.
        </p>
    </div>

    {{-- Session Status --}}
    @if(session('status'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium"
        style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;">
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div class="relative group">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors duration-200"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        style="--tw-text-opacity:1;">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </div>
                <input id="email"
                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800
                              border border-gray-200 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 rounded-xl
                              focus:ring-2 focus:border-transparent
                              transition-all duration-200 outline-none
                              placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm
                              @error('email') border-red-400 bg-red-50 @enderror"
                    style="--tw-ring-color: var(--siakad-primary);"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required autofocus autocomplete="username"
                    placeholder="Email address" />
            </div>
            @error('email')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="relative group">
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors duration-200"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0
                                 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="password"
                    class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 dark:bg-gray-800
                              border border-gray-200 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 rounded-xl
                              focus:ring-2 focus:border-transparent
                              transition-all duration-200 outline-none
                              placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required autocomplete="current-password"
                    placeholder="Password" />
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center
                               text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="h-5 w-5" x-show="!show" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542
                                 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg class="h-5 w-5" x-show="show" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05
                                 10.05 0 011.577-2.387M8 8.05A2.992 2.992 0 007.828 10.828l3.125 3.125a2.991
                                 2.991 0 003.354-.055m1.515-2.074a2.992 2.992 0 00-.776-3.875" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                    </svg>
                </button>
            </div>
            @error('password')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                    class="w-4 h-4 border border-gray-300 rounded
                              focus:ring-2 transition duration-150 ease-in-out
                              dark:bg-gray-800 dark:border-gray-600"
                    style="color: var(--siakad-primary); --tw-ring-color: rgba(35,76,106,0.2);">
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400
                             group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                    Ingat saya 30 hari
                </span>
            </label>
        </div>

        {{-- Submit --}}
        <div class="pt-2">
            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent
                           rounded-xl shadow-sm text-sm font-semibold text-white
                           focus:outline-none focus:ring-2 focus:ring-offset-2
                           transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);
                           --tw-ring-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                Masuk
            </button>
        </div>

        {{-- PPDB link --}}
        <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-6">
            Calon santri baru?
            <a href="{{ route('ppdb.public.index') }}"
                class="font-medium transition-colors"
                style="color: var(--siakad-primary);"
                onmouseover="this.style.color='var(--siakad-dark)'"
                onmouseout="this.style.color='var(--siakad-primary)'">
                Daftar PPDB Online →
            </a>
            <!-- KOTAK LINK KE PORTAL SIMAQ (Instan Render) -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="{{ route('simaq.login') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(34, 197, 94, 0.2); background-color: rgba(34, 197, 94, 0.05); text-decoration: none; transition: all 0.3s;" onmouseover="this.style.backgroundColor='rgba(34, 197, 94, 0.1)'; this.style.borderColor='rgba(34, 197, 94, 0.4)';" onmouseout="this.style.backgroundColor='rgba(34, 197, 94, 0.05)'; this.style.borderColor='rgba(34, 197, 94, 0.2)';">

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Icon Buku Al-Qur'an -->
                    <div style="padding: 0.5rem; background-color: rgba(34, 197, 94, 0.1); border-radius: 0.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: #22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>

                    <!-- Teks -->
                    <div style="text-align: left;">
                        <h3 style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #4ade80;">
                            Guru Tahsin-Tahfizh? Masuk SIMAQ
                        </h3>
                        <p style="margin: 0; font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">
                            Portal khusus Mutaba'ah Al-Qur'an
                        </p>
                    </div>
                </div>

                <!-- Panah Kanan -->
                <svg style="width: 1.25rem; height: 1.25rem; color: rgba(34, 197, 94, 0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        </p>
    </form>
</x-guest-layout>