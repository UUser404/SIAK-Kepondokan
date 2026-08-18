<x-app-layout>
    {{-- ============================================================ --}}
    {{-- Dashboard Guru                                               --}}
    {{-- Diganti dari basis jadwal (hari/jam) ke basis Penugasan       --}}
    {{-- Mengajar (guru pilih sendiri kelas & tanggal presensi)        --}}
    {{-- ============================================================ --}}
    <x-slot name="header">Dashboard</x-slot>

    {{-- Greeting --}}
    <div class="mb-8">
        @php
        $hour = now()->hour;
        if ($hour < 11) { $greeting='Selamat Pagi' ; $emoji='🌅' ; }
            elseif ($hour < 15) { $greeting='Selamat Siang' ; $emoji='☀️' ; }
            elseif ($hour < 18) { $greeting='Selamat Sore' ; $emoji='🌤️' ; }
            else { $greeting='Selamat Malam' ; $emoji='🌙' ; }
            @endphp
            <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }}! {{ $emoji }}
            </h1>
            <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
                Berikut ringkasan kelas & mata pelajaran yang Anda ampu
            </p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'value'=>$totalMapel, 'label'=>'Mata Pelajaran Diampu', 'route'=>null],
        ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'value'=>$totalKelas, 'label'=>'Total Kelas', 'route'=>null],
        ['icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'value'=>$pertemuanBulanIni, 'label'=>'Pertemuan Bulan Ini', 'route'=>route('guru.jurnal.index')],
        ['icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'value'=>$nilaiPending, 'label'=>'Nilai Pending', 'route'=>route('guru.nilai.index')],
        ] as $stat)
        @if($stat['route'])
        <a href="{{ $stat['route'] }}" class="card-saas p-5 block transition hover:-translate-y-0.5 hover:shadow-md">
            @else
            <div class="card-saas p-5">
                @endif
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: rgba(35,76,106,0.1);">
                        <svg class="w-5 h-5" style="color: var(--siakad-primary);"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $stat['value'] }}</p>
                        <p class="text-xs" style="color: var(--siakad-secondary);">{{ $stat['label'] }}</p>
                    </div>
                </div>
                @if($stat['route'])
        </a>
        @else
    </div>
    @endif
    @endforeach
    </div>

    {{-- Pengingat Wali Kelas -- SEBELUMNYA tidak ada sama sekali di dashboard
         ini, padahal guru yang kebetulan juga wali kelas gampang kelupaan
         cek tugasnya kalau tidak notice sendiri section sidebar. Kalau cuma
         1 kelas, arahkan langsung ke Predikat Sikap (tugas yang paling
         gampang kelupaan diisi manual). Kalau >1 kelas, arahkan ke Dashboard
         Wali Kelas (overview semua kelasnya) -- konsisten sama logic yang
         sama di sidebar-nav.blade.php (guru-sidebar-section). --}}
    @if($kelasWaliList->isNotEmpty())
    <a href="{{ $kelasWaliList->count() === 1
            ? route('wali-kelas.predikat-sikap.index', $kelasWaliList->first())
            : route('wali-kelas.dashboard') }}"
        class="card-saas p-5 mb-6 flex items-center justify-between gap-3 transition hover:-translate-y-0.5 hover:shadow-md"
        style="background: linear-gradient(to right, rgba(35,76,106,0.06), transparent); border-left: 3px solid var(--siakad-primary);">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background-color: rgba(35,76,106,0.1);">
                <svg class="w-5 h-5" style="color: var(--siakad-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold" style="color: var(--siakad-dark);">
                    Anda Wali Kelas
                    {{ $kelasWaliList->count() === 1 ? $kelasWaliList->first()->nama : "({$kelasWaliList->count()} kelas)" }}
                </p>
                <p class="text-xs" style="color: var(--siakad-secondary);">
                    @if($kelasWaliList->count() === 1)
                    Jangan lupa isi Predikat Sikap santri
                    @else
                    {{ $kelasWaliList->pluck('nama')->join(', ') }}
                    @endif
                </p>
            </div>
        </div>
        <svg class="w-5 h-5 flex-shrink-0" style="color: var(--siakad-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </a>
    @endif

    {{-- Kelas & Mapel yang Diampu --}}
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b flex items-center justify-between" style="border-color: var(--border-color);">
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Kelas & Mata Pelajaran yang Diampu</h3>
            <a href="{{ route('guru.presensi.index') }}" class="text-xs font-medium transition-colors" style="color: var(--siakad-primary);">
                Lihat semua →
            </a>
        </div>

        @if($penugasanList->isNotEmpty())
        <div class="divide-y" style="border-color: var(--border-color);">
            @foreach($penugasanList->take(6) as $penugasan)
            <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium" style="color: var(--siakad-dark);">
                        {{ $penugasan->mataPelajaran->nama }}
                    </p>
                    <p class="text-xs" style="color: var(--siakad-secondary);">
                        {{ $penugasan->kelas->nama }}
                    </p>
                </div>
                @if($penugasan->sudahPresensi)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                 rounded-xl bg-green-50 text-green-700 border border-green-200">
                    ✓ Sudah diinput hari ini
                </span>
                @else
                <a href="{{ route('guru.presensi.create', $penugasan->id) }}"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white transition"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Input Presensi
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="p-8 text-center">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm" style="color: var(--siakad-secondary);">
                Belum ada penugasan mengajar dari Kurikulum. Hubungi Wakil Kurikulum untuk pengaturan kelas & mapel Anda.
            </p>
        </div>
        @endif
    </div>

</x-app-layout>