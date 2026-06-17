<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIAK Kepondokan') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Dark Mode Flash Prevention -->
        <script>
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
            (function() {
                var sidebarState = localStorage.getItem('sidebarOpen');
                if (sidebarState === 'false') {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --siakad-dark: #1B3C53;
                --siakad-primary: #234C6A;
                --siakad-secondary: #456882;
                --siakad-light: #E3E3E3;
                --bg-body: #FAFBFC;
                --bg-card: #FFFFFF;
                --bg-sidebar: #FFFFFF;
                --text-primary: #1B3C53;
                --text-secondary: #456882;
                --border-color: #E3E3E3;
            }

            html { scroll-behavior: smooth; }

            .dark {
                --bg-body: #111827;
                --bg-card: #1F2937;
                --bg-sidebar: #1F2937;
                --text-primary: #FFFFFF;
                --text-secondary: #9CA3AF;
                --border-color: #374151;
            }

            body {
                font-family: 'Inter', system-ui, sans-serif;
                -webkit-font-smoothing: antialiased;
                background-color: var(--bg-body);
                color: var(--text-primary);
                transition: background-color 0.3s ease, color 0.3s ease;
            }

            /* Sidebar Links */
            .sidebar-link {
                transition: all 0.15s ease;
                position: relative;
            }
            .sidebar-link:hover { background-color: rgba(35, 76, 106, 0.08); }
            .sidebar-link.active { background-color: var(--siakad-primary); color: white; }
            .sidebar-link.active:hover { background-color: var(--siakad-dark); }

            /* Cards */
            .card-saas {
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                transition: all 0.2s ease;
            }
            .card-saas:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .dark .card-saas:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.3); }

            /* Dark Mode */
            .dark .text-siakad-dark    { color: var(--text-primary) !important; }
            .dark .text-siakad-secondary { color: #D1D5DB !important; }
            .dark .text-siakad-primary { color: #60A5FA !important; }
            .dark .border-siakad-light { border-color: var(--border-color) !important; }
            .dark .bg-siakad-light     { background-color: #334155 !important; }

            /* Buttons */
            .btn-primary-saas {
                background-color: var(--siakad-primary);
                color: white;
                transition: all 0.15s ease;
            }
            .btn-primary-saas:hover {
                background-color: var(--siakad-dark);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(27,60,83,0.3);
            }

            .btn-ghost-saas {
                background-color: transparent;
                color: var(--siakad-primary);
                border: 1px solid var(--siakad-light);
                transition: all 0.15s ease;
            }
            .btn-ghost-saas:hover {
                background-color: rgba(35,76,106,0.08);
                border-color: var(--siakad-secondary);
            }

            /* Inputs */
            .input-saas {
                border: 1px solid var(--siakad-light);
                border-radius: 8px;
                transition: all 0.15s ease;
                background-color: var(--bg-card);
                color: var(--text-primary);
            }
            .dark .input-saas {
                background-color: var(--bg-sidebar);
                border-color: var(--border-color);
            }
            .input-saas:focus {
                border-color: var(--siakad-primary);
                box-shadow: 0 0 0 3px rgba(35,76,106,0.1);
                outline: none;
            }

            /* Tables */
            .table-saas tbody tr { transition: background-color 0.15s ease; }
            .table-saas tbody tr:hover { background-color: rgba(35,76,106,0.04); }

            /* Scrollbar */
            ::-webkit-scrollbar { width: 6px; height: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: var(--siakad-light); border-radius: 3px; }
            ::-webkit-scrollbar-thumb:hover { background: var(--siakad-secondary); }

            /* Animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-4px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in { animation: fadeIn 0.2s ease-out; }

            /* Sidebar Collapse */
            aside { transition: width 0.3s cubic-bezier(0.4,0,0.2,1); }

            .sidebar-text,
            .sidebar-logo-text,
            .sidebar-section-title,
            .sidebar-user-info {
                transition: opacity 0.2s ease, transform 0.2s ease;
                opacity: 1;
                transform: translateX(0);
            }
            .sidebar-collapsed .sidebar-text,
            .sidebar-collapsed .sidebar-logo-text,
            .sidebar-collapsed .sidebar-section-title,
            .sidebar-collapsed .sidebar-user-info {
                opacity: 0;
                transform: translateX(-10px);
                pointer-events: none;
                width: 0;
                overflow: hidden;
                white-space: nowrap;
            }
            .sidebar-link { transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
            .sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
                gap: 0;
            }
            .sidebar-link svg { transition: margin 0.25s ease; flex-shrink: 0; }
            .sidebar-collapsed .sidebar-link svg { margin: 0; }

            .user-section { transition: justify-content 0.25s ease; }
            .sidebar-collapsed .user-section { justify-content: center; gap: 0; }

            .sidebar-toggle-icon { transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); }
            .sidebar-collapsed .sidebar-toggle-icon { transform: rotate(180deg); }

            .logo-section { transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
            .sidebar-collapsed .logo-section { justify-content: center; padding-left: 0; padding-right: 0; }
            .sidebar-collapsed .logo-section > div { justify-content: center; gap: 0; }

            .toggle-btn { transition: opacity 0.2s ease; opacity: 1; }
            .sidebar-collapsed .toggle-btn { opacity: 0; pointer-events: none; position: absolute; }

            /* Init collapsed state (before Alpine loads) */
            .sidebar-collapsed-init aside { width: 5rem !important; }
            .sidebar-collapsed-init .sidebar-text,
            .sidebar-collapsed-init .sidebar-logo-text,
            .sidebar-collapsed-init .sidebar-section-title,
            .sidebar-collapsed-init .sidebar-user-info {
                opacity: 0 !important; width: 0 !important; overflow: hidden !important;
            }
            .sidebar-collapsed-init .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; gap: 0; }
            .sidebar-collapsed-init .logo-section { justify-content: center; }
            .sidebar-collapsed-init .logo-section > div { justify-content: center; gap: 0; }
            .sidebar-collapsed-init .user-section { justify-content: center; gap: 0; }
            .sidebar-collapsed-init .toggle-btn { opacity: 0; pointer-events: none; }

            [x-cloak] { display: none !important; }
            .no-transition * { transition: none !important; }
        </style>

        <script>
            function toggleDarkMode() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark);
                document.getElementById('moonIcon')?.classList.toggle('hidden', isDark);
                document.getElementById('sunIcon')?.classList.toggle('hidden', !isDark);
            }
        </script>
    </head>
    <body class="antialiased no-transition"
          x-data="{
              sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
              mobileSidebarOpen: false
          }"
          :class="{ 'sidebar-collapsed': !sidebarOpen }"
          x-init="
              setTimeout(() => document.body.classList.remove('no-transition'), 100);
              $watch('sidebarOpen', val => {
                  localStorage.setItem('sidebarOpen', val);
                  document.documentElement.classList.toggle('sidebar-collapsed-init', !val);
              });
          ">

        <!-- Mobile Sidebar Overlay -->
        <div x-cloak x-show="mobileSidebarOpen"
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 z-30 bg-gray-900/50 backdrop-blur-sm md:hidden transition-opacity duration-300">
        </div>

        <div class="min-h-screen flex">

            {{-- =================== SIDEBAR =================== --}}
            <aside class="fixed inset-y-0 left-0 z-40 w-64 border-r transition-transform duration-300
                          transform md:translate-x-0 md:sticky md:top-0 md:h-screen"
                   style="background-color: var(--bg-sidebar); border-right: 1px solid var(--border-color);"
                   :class="{
                       'translate-x-0': mobileSidebarOpen,
                       '-translate-x-full': !mobileSidebarOpen,
                       'w-64': sidebarOpen,
                       'w-20': !sidebarOpen && !mobileSidebarOpen
                   }">

                {{-- Logo --}}
                <div class="h-16 flex items-center justify-between px-4 logo-section"
                     style="border-bottom: 1px solid var(--border-color);">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <button @click="if(!sidebarOpen) sidebarOpen = true"
                                :class="!sidebarOpen ? 'cursor-pointer' : 'cursor-default'"
                                class="w-9 h-9 rounded-lg bg-siakad-primary flex items-center
                                       justify-center flex-shrink-0 transition"
                                style="background-color: var(--siakad-primary);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                                         C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477
                                         14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247
                                         18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </button>
                        <div class="sidebar-logo-text">
                            <h1 class="text-base font-semibold" style="color: var(--text-primary);">
                                {{ config('app.name') }}
                            </h1>
                            <p class="text-[11px] tracking-wide" style="color: var(--text-secondary);">
                                Pondok Pesantren Modern
                            </p>
                        </div>
                    </div>
                    {{-- Desktop Toggle --}}
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden md:block p-2 rounded-lg transition flex-shrink-0 toggle-btn"
                            style="color: var(--text-secondary);">
                        <svg class="w-4 h-4 sidebar-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                    {{-- Mobile Close --}}
                    <button @click="mobileSidebarOpen = false"
                            class="md:hidden p-2 rounded-lg transition flex-shrink-0"
                            style="color: var(--text-secondary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Navigation --}}
                <nav class="p-3 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 180px);">
                    @include('layouts.partials.sidebar-nav')
                </nav>

                {{-- User info --}}
                <div class="absolute bottom-0 left-0 right-0 p-3"
                     style="border-top: 1px solid var(--border-color); background-color: var(--bg-sidebar);">
                    <div class="flex items-center gap-3 user-section">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                    text-white text-sm font-semibold flex-shrink-0"
                             style="background-color: var(--siakad-primary);">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 sidebar-user-info">
                            <p class="text-sm font-medium truncate" style="color: var(--text-primary);">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-[11px] capitalize" style="color: var(--text-secondary);">
                                {{ str_replace('_', ' ', Auth::user()->role) }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="sidebar-user-info">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg transition-colors"
                                    style="color: var(--text-secondary);" title="Logout">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0
                                             01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            {{-- =================== MAIN =================== --}}
            <div class="flex-1 transition-all duration-300 min-w-0">

                {{-- Topbar --}}
                <header class="h-16 flex items-center justify-between px-4 md:px-8 sticky top-0 z-20"
                        style="background-color: var(--bg-card); border-bottom: 1px solid var(--border-color);">
                    <div class="flex items-center gap-3">
                        {{-- Mobile hamburger --}}
                        <button @click="mobileSidebarOpen = true"
                                class="md:hidden p-2 -ml-2 rounded-lg transition"
                                style="color: var(--text-secondary);">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        @isset($header)
                            <h1 class="text-lg font-semibold truncate max-w-[200px] md:max-w-none"
                                style="color: var(--text-primary);">{{ $header }}</h1>
                        @endisset
                    </div>

                    <div class="flex items-center gap-2 md:gap-4">
                        {{-- Tahun Ajaran aktif --}}
                        @php $taAktif = \App\Models\TahunAjaran::aktif(); @endphp
                        @if($taAktif)
                            <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-lg text-xs
                                         font-medium border"
                                  style="background-color: rgba(35,76,106,0.08);
                                         color: var(--siakad-primary);
                                         border-color: rgba(35,76,106,0.2);">
                                {{ $taAktif->nama }} · {{ ucfirst($taAktif->semester) }}
                            </span>
                        @endif

                        {{-- Dark Mode Toggle --}}
                        <button onclick="toggleDarkMode()"
                                class="p-2 rounded-lg transition-colors"
                                style="color: var(--text-secondary);"
                                title="Toggle Dark Mode">
                            <svg id="moonIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <svg id="sunIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343
                                         6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4
                                         4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>
                        <script>
                            if (localStorage.getItem('darkMode') === 'true') {
                                document.getElementById('moonIcon')?.classList.add('hidden');
                                document.getElementById('sunIcon')?.classList.remove('hidden');
                            }
                        </script>

                        {{-- Nama user --}}
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium" style="color: var(--text-primary);">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-[11px]" style="color: var(--text-secondary);">
                                {{ now()->locale('id')->isoFormat('ddd, D MMM Y') }}
                            </p>
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="p-4 md:p-8">
                    @if(session('success'))
                        <div class="mb-6 px-4 py-3 rounded-lg text-sm font-medium animate-fade-in"
                             style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 px-4 py-3 rounded-lg text-sm font-medium animate-fade-in"
                             style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
