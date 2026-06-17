<x-app-layout>
    {{-- ============================================================ --}}
    {{-- Dashboard Guru                                               --}}
    {{-- ============================================================ --}}
    <x-slot name="header">Dashboard</x-slot>

    {{-- Greeting --}}
    <div class="mb-8">
        @php
            $hour = now()->hour;
            if ($hour < 11) { $greeting = 'Selamat Pagi'; $emoji = '🌅'; }
            elseif ($hour < 15) { $greeting = 'Selamat Siang'; $emoji = '☀️'; }
            elseif ($hour < 18) { $greeting = 'Selamat Sore'; $emoji = '🌤️'; }
            else { $greeting = 'Selamat Malam'; $emoji = '🌙'; }
        @endphp
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }}! {{ $emoji }}
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Berikut ringkasan aktivitas mengajar Anda hari ini
        </p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['icon'=>'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z', 'value'=>$jadwalHariIni,  'label'=>'Jadwal Hari Ini'],
            ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'value'=>$totalKelas, 'label'=>'Total Kelas'],
            ['icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'value'=>$belumPresensi, 'label'=>'Belum Presensi'],
            ['icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'value'=>$nilaiPending, 'label'=>'Nilai Pending'],
        ] as $stat)
        <div class="card-saas p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background-color: rgba(35,76,106,0.1);">
                    <svg class="w-5 h-5" style="color: var(--siakad-primary);"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $stat['value'] }}</p>
                    <p class="text-xs" style="color: var(--siakad-secondary);">{{ $stat['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Hero Card: Kelas hari ini --}}
    @if($jadwalHariIniList->isNotEmpty())
    @php $pertama = $jadwalHariIniList->first(); @endphp
    <div class="card-saas p-6 mb-6 border-l-4"
         style="border-left-color: var(--siakad-primary);
                background: linear-gradient(to right, white, rgba(35,76,106,0.03));">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 rounded text-white text-xs font-bold uppercase tracking-wide"
                          style="background-color: var(--siakad-primary);">Kelas Hari Ini</span>
                    <span class="text-sm font-semibold" style="color: var(--siakad-primary);">
                        {{ \Carbon\Carbon::parse($pertama->jam_mulai)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($pertama->jam_selesai)->format('H:i') }}
                    </span>
                </div>
                <h2 class="text-2xl font-bold mb-1" style="color: var(--siakad-dark);">
                    {{ $pertama->mataPelajaran->nama }}
                </h2>
                <p style="color: var(--siakad-secondary);">
                    Kelas {{ $pertama->kelas->nama }} · {{ $pertama->ruangan ?? 'Ruang -' }}
                </p>
            </div>
            @if(!$pertama->sudahPresensi)
            <a href="{{ route('guru.presensi.create', $pertama->id) }}"
               class="flex-shrink-0 flex items-center justify-center gap-2 px-6 py-4
                      text-white text-base font-bold rounded-xl shadow-lg
                      hover:scale-105 transition transform w-full md:w-auto"
               style="background-color: var(--siakad-primary);"
               onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
               onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                             00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2
                             2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Buka Presensi
            </a>
            @else
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                         bg-green-50 text-green-700 border border-green-200">
                ✓ Sudah diinput
            </span>
            @endif
        </div>
    </div>

    {{-- Jadwal lain hari ini --}}
    @if($jadwalHariIniList->count() > 1)
    <div class="card-saas mb-6 overflow-hidden">
        <div class="px-5 py-3.5 border-b" style="border-color: var(--border-color);">
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Jadwal Lainnya</h3>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @foreach($jadwalHariIniList->skip(1) as $jadwal)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color: var(--siakad-dark);">
                        {{ $jadwal->mataPelajaran->nama }}
                    </p>
                    <p class="text-xs" style="color: var(--siakad-secondary);">
                        {{ $jadwal->kelas->nama }} ·
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}
                    </p>
                </div>
                @if($jadwal->sudahPresensi)
                    <span class="text-xs font-medium text-green-600">✓ Sudah</span>
                @else
                    <a href="{{ route('guru.presensi.create', $jadwal->id) }}"
                       class="text-xs font-medium px-3 py-1.5 rounded-lg text-white transition"
                       style="background-color: var(--siakad-primary);">
                        Input
                    </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @else
    <div class="card-saas p-8 text-center mb-6">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm" style="color: var(--siakad-secondary);">Tidak ada jadwal mengajar hari ini</p>
    </div>
    @endif

</x-app-layout>
