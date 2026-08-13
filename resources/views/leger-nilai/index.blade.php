{{-- ============================================================ --}}
{{-- resources/views/leger-nilai/index.blade.php                 --}}
{{-- Pemilih kelas -- pola sama dengan rapor/index.blade.php,      --}}
{{-- dipakai bersama Kurikulum & WaliKelas (lihat catatan          --}}
{{-- routePrefix di situ untuk alasan lengkap).                    --}}
{{-- ============================================================ --}}
@php
$routePrefix = request()->routeIs('wali-kelas.*') ? 'wali-kelas' : 'kurikulum';
@endphp
<x-app-layout>
    <x-slot name="header">Leger Nilai</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Leger Nilai</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Daftar Kumpulan Nilai Semester -- pilih kelas untuk lihat rekap nilai satu kelas penuh
            </p>
        </div>
        @if($ta)
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-medium border"
            style="background-color: rgba(35,76,106,0.08);
                 color: var(--siakad-primary);
                 border-color: rgba(35,76,106,0.2);">
            {{ $ta->nama_lengkap }}
        </span>
        @endif
    </div>

    @if(!$ta)
    <div class="p-4 mb-6 rounded-xl text-sm" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e;">
        Belum ada tahun ajaran aktif.
    </div>
    @endif

    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Pilih Kelas</h3>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @forelse($kelasList as $k)
            <a href="{{ route($routePrefix.'.leger-nilai.show', $k) }}"
                class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold
                            text-sm flex-shrink-0"
                        style="background-color: var(--siakad-primary);">
                        {{ strtoupper(substr($k->nama, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-siakad-dark">{{ $k->nama }}</p>
                        <p class="text-xs text-siakad-secondary">
                            {{ $k->tingkatan->nama ?? '-' }} · Wali Kelas: {{ $k->waliKelas?->name ?? '-' }}
                            · {{ $k->jumlah_santri }} santri
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-siakad-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @empty
            <div class="px-5 py-12 text-center text-sm text-siakad-secondary">
                @if($routePrefix === 'wali-kelas')
                Anda belum ditugaskan sebagai wali kelas manapun di tahun ajaran aktif.
                @else
                Belum ada kelas di tahun ajaran aktif.
                @endif
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>