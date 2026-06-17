<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIAK Kepondokan') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --siakad-dark: #1B3C53;
            --siakad-primary: #234C6A;
            --siakad-secondary: #456882;
        }
        body { font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-900 selection:bg-siakad-primary selection:text-white">
    <div class="min-h-screen flex">

        {{-- Kiri: Form --}}
        <div class="w-full lg:w-[480px] xl:w-[560px] flex flex-col justify-center px-8 lg:px-16
                    relative z-10 bg-white dark:bg-gray-900">

            {{-- Mobile Logo --}}
            <div class="lg:hidden absolute top-8 left-8">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
                         style="background-color: var(--siakad-primary);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                                     C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477
                                     14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247
                                     18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl" style="color: var(--siakad-dark);">
                        {{ config('app.name') }}
                    </span>
                </a>
            </div>

            <div class="w-full max-w-[400px] mx-auto">
                {{ $slot }}
            </div>

            <div class="absolute bottom-8 left-0 right-0 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>

        {{-- Kanan: Visual --}}
        <div class="hidden lg:flex flex-1 relative overflow-hidden items-center justify-center"
             style="background-color: var(--siakad-dark);">
            {{-- Gradients --}}
            <div class="absolute inset-0 bg-gradient-to-br from-[#1B3C53] via-[#163247] to-gray-900"></div>
            <div class="absolute top-0 right-0 w-[800px] h-[800px] rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"
                 style="background: rgba(35,76,106,0.2);"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"
                 style="background: rgba(99,102,241,0.1);"></div>

            {{-- Pattern --}}
            <div class="absolute inset-0 opacity-10"
                 style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
            </div>

            {{-- Card glassmorphism --}}
            <div class="relative z-10 max-w-lg text-center p-12">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl
                            relative overflow-hidden group hover:bg-white/10 transition-colors duration-500">
                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent
                                opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-cyan-300 rounded-2xl mx-auto
                                mb-8 flex items-center justify-center shadow-lg shadow-indigo-500/30
                                transform group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                                     C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477
                                     14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247
                                     18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>

                    <h2 class="text-3xl font-bold text-white mb-4 tracking-tight">SIAK Kepondokan</h2>
                    <p class="text-indigo-100/80 text-lg leading-relaxed">
                        Sistem Informasi Akademik Pondok Pesantren Modern Al Islam — terintegrasi, efisien, berbasis AI.
                    </p>

                    {{-- Feature list --}}
                    <div class="mt-8 space-y-3 text-left">
                        @foreach([
                            ['Manajemen santri & presensi terpadu'],
                            ['Penilaian otomatis dengan kalkulasi bobot'],
                            ['AI Advisor untuk analisis akademik'],
                            ['PPDB Online & surat-menyurat digital'],
                        ] as [$feat])
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-cyan-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0
                                             011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-white/70 text-sm">{{ $feat }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-center gap-2">
                        <div class="w-12 h-1 bg-white/30 rounded-full"></div>
                        <div class="w-2 h-1 bg-white/10 rounded-full"></div>
                        <div class="w-2 h-1 bg-white/10 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
