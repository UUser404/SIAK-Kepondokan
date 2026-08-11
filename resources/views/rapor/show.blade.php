{{-- ============================================================ --}}
{{-- resources/views/rapor/show.blade.php                        --}}
{{-- Preview rapor satu santri - Dengan Sticky Header            --}}
{{-- ============================================================ --}}
@php
$routePrefix = request()->routeIs('wali-kelas.*') ? 'wali-kelas' : 'kurikulum';
@endphp
<x-app-layout>
    <x-slot name="header">Rapor — {{ $santri->nama_lengkap }}</x-slot>

    {{-- ============================================================ --}}
    {{-- STICKY CONTAINER - Selalu di atas saat scroll               --}}
    {{-- ============================================================ --}}
    <div class="sticky top-0 z-20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700 transition-shadow" id="sticky-header">

        {{-- Tombol Kembali & Nama Santri --}}
        <div class="mb-3 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route($routePrefix.".rapor.index") }}"
                    class="p-2 rounded-xl border border-gray-200
                      text-siakad-secondary
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-xl font-semibold text-siakad-dark">
                        {{ $santri->nama_lengkap }}
                    </h2>
                    <p class="text-sm text-siakad-secondary">
                        NIS: {{ $santri->nis }}
                        @if($kelasAktif) · {{ $kelasAktif->nama }} @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
                <a href="{{ route($routePrefix.".rapor.cetak", $santri) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                      text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2
                             4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2
                             0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden sm:inline">Cetak PDF</span>
                    <span class="sm:hidden">PDF</span>
                </a>
                <a href="{{ route($routePrefix.'.rapor.cetak-arab', $santri) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                      border border-gray-200 text-siakad-secondary
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2
                             4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2
                             0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden sm:inline">Cetak Rapor (Arab)</span>
                    <span class="sm:hidden">Arab</span>
                </a>
            </div>
        </div>

        {{-- SUMMARY KARTU --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach([
            ['Rata-rata Nilai', $rataRata, $rataRata >= 70 ? 'green' : 'red'],
            ['Mapel Tuntas', "{$totalTuntas}/{$totalMapel}", $totalTuntas === $totalMapel ? 'green' : 'yellow'],
            ['Predikat Umum', $rataRata >= 90 ? 'A' : ($rataRata >= 80 ? 'B' : ($rataRata >= 70 ? 'C' : 'D')), 'blue'],
            ] as [$label, $value, $color])
            <div class="card-saas p-3 sm:p-4 text-center">
                <p class="text-xl sm:text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                    {{ $value }}
                </p>
                <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KONTEN UTAMA - Memberi jarak agar tidak ketutupan header    --}}
    {{-- ============================================================ --}}
    <div class="space-y-5 max-w-7xl mx-auto mt-4">

        {{-- Tabel nilai --}}
        <div class="card-saas overflow-hidden">
            <div class="px-4 py-3 flex items-center gap-2 flex-wrap"
                style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full flex-shrink-0" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Nilai Per Mata Pelajaran — {{ $ta?->nama_lengkap }}
                </h3>
                <span class="text-xs text-siakad-secondary ml-auto hidden sm:inline">
                    Scroll horizontal →
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas min-w-[900px]">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['#','Mata Pelajaran','UH','Tugas','Praktik','UTS','UAS','Nilai Akhir','Predikat','KKM','Status','H','S','I','A'] as $h)
                            <th class="px-2 sm:px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary whitespace-nowrap
                                   first:text-left first:pl-4 sm:first:pl-5">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($nilaiAkhir as $i => $na)
                        @php $kehadiran = $kehadiranMapel[$na->mata_pelajaran_id] ?? null; @endphp
                        <tr class="{{ $na->tuntas ? '' : 'bg-red-50/30 dark:bg-red-950/20' }}">
                            <td class="px-2 sm:px-3 py-3 text-xs text-siakad-secondary text-center">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-2 sm:px-3 py-3 font-medium text-siakad-dark whitespace-nowrap">
                                {{ $na->mataPelajaran->nama }}
                            </td>
                            @foreach(['nilai_uh','nilai_tugas','nilai_praktik','nilai_uts','nilai_uas'] as $field)
                            <td class="px-2 sm:px-3 py-3 text-center text-siakad-secondary text-xs sm:text-sm">
                                {{ $na->$field ? number_format($na->$field, 1) : '—' }}
                            </td>
                            @endforeach
                            <td class="px-2 sm:px-3 py-3 text-center font-bold text-xs sm:text-sm
                                   {{ $na->tuntas ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $na->nilai_akhir ? number_format($na->nilai_akhir, 1) : '—' }}
                            </td>
                            <td class="px-2 sm:px-3 py-3 text-center font-semibold text-siakad-dark text-xs sm:text-sm">
                                {{ $na->predikat ?? '—' }}
                            </td>
                            <td class="px-2 sm:px-3 py-3 text-center text-siakad-secondary text-xs sm:text-sm">
                                {{ $na->kkm_tingkatan ?? '—' }}
                            </td>
                            <td class="px-2 sm:px-3 py-3 text-center">
                                <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $na->tuntas
                                   ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300'
                                   : 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' }}">
                                    {{ $na->tuntas ? 'T' : 'BT' }}
                                </span>
                            </td>
                            {{-- Kehadiran --}}
                            @foreach(['hadir','sakit','izin','alpa'] as $s)
                            <td class="px-1 sm:px-3 py-3 text-center text-xs
                                   {{ $s === 'alpa' && ($kehadiran[$s] ?? 0) > 0
                                      ? 'text-red-600 dark:text-red-400 font-semibold'
                                      : 'text-siakad-secondary' }}">
                                {{ $kehadiran[$s] ?? 0 }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Indikator scroll di mobile --}}
            <div class="sm:hidden px-4 py-2 text-center text-xs text-siakad-secondary border-t" style="border-color: var(--border-color);">
                ↕ Geser tabel untuk melihat semua kolom
            </div>
        </div>

    </div>
</x-app-layout>