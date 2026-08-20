{{-- ============================================================ --}}
{{-- resources/views/leger-nilai/show.blade.php                  --}}
{{-- Matrix leger 1 kelas penuh -- semua santri x semua mapel yang --}}
{{-- ditugaskan ke kelas ini. $data datang dari                    --}}
{{-- PenilaianService::getLegerKelas(), lihat komentar di sana     --}}
{{-- untuk struktur array-nya.                                     --}}
{{-- ============================================================ --}}
@php
$routePrefix = request()->routeIs('wali-kelas.*') ? 'wali-kelas' : 'kurikulum';
@endphp
<x-app-layout>
    <x-slot name="header">Leger Nilai — {{ $kelas->nama }}</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route($routePrefix.'.leger-nilai.index') }}"
                class="p-2 rounded-xl border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-siakad-dark">{{ $kelas->nama }}</h2>
                <p class="text-sm text-siakad-secondary mt-0.5">
                    {{ $kelas->tingkatan->nama ?? '-' }} · Wali Kelas: {{ $kelas->waliKelas?->name ?? '-' }}
                    @if($ta) · {{ $ta->nama_lengkap }} @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route($routePrefix.'.leger-nilai.cetak', $kelas) }}" target="_blank"
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
                Cetak PDF
            </a>
            <a href="{{ route($routePrefix.'.leger-nilai.export', $kelas) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                  border transition hover:bg-gray-50 dark:hover:bg-gray-700"
                style="border-color: var(--border-color); color: var(--siakad-secondary);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H6a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                         1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    @if($data['mapelList']->isEmpty())
    <div class="card-saas p-16 text-center">
        <p class="text-siakad-secondary text-sm">
            Belum ada mata pelajaran yang ditugaskan ke kelas ini di tahun ajaran aktif.
            Atur lewat menu Penugasan Mengajar dulu.
        </p>
    </div>
    @else
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary sticky left-0 z-10 align-bottom"
                            style="background-color: rgba(35,76,106,0.04);">No</th>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary sticky left-10 z-10 min-w-[180px] align-bottom"
                            style="background-color: rgba(35,76,106,0.04);">Nama Santri</th>
                        <th rowspan="2" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary border-l min-w-[140px] align-bottom" style="border-color: var(--border-color);">Nama Arab</th>
                        @foreach($data['mapelList'] as $mapel)
                        <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary border-l min-w-[70px]" style="border-color: var(--border-color);">
                            {{ $mapel->nama }}
                        </th>
                        @endforeach
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                           border-l align-bottom" style="color: var(--siakad-primary); border-color: var(--border-color);">Jumlah</th>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary align-bottom">Rata-rata</th>
                        <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary align-bottom">Peringkat</th>
                        <th colspan="3" class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary border-l" style="border-color: var(--border-color);">Ketidakhadiran</th>
                        <th colspan="4" class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide
                           text-siakad-secondary border-l" style="border-color: var(--border-color);">Kepribadian</th>
                    </tr>
                    <tr>
                        @foreach($data['mapelList'] as $mapel)
                        <th class="px-2 py-1.5 text-center text-[10px] font-medium text-siakad-secondary border-l"
                            style="border-color: var(--border-color);">
                            KKM {{ $data['kkmPerMapel'][$mapel->id] ?? '—' }}
                        </th>
                        @endforeach
                        @foreach(['S','I','A'] as $h)
                        <th class="px-1 py-1.5 text-center text-[10px] font-medium text-siakad-secondary border-l"
                            style="border-color: var(--border-color);">{{ $h }}</th>
                        @endforeach
                        @foreach(['Akhlaq','Rajin','Bersih','Disiplin'] as $h)
                        <th class="px-1 py-1.5 text-center text-[10px] font-medium text-siakad-secondary border-l"
                            style="border-color: var(--border-color);">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($data['rows'] as $i => $row)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-3 py-2.5 text-center text-xs text-siakad-secondary sticky left-0 bg-white z-10">
                            {{ $i + 1 }}
                        </td>
                        <td class="px-4 py-2.5 sticky left-10 bg-white z-10" style="border-right: 1px solid var(--border-color);">
                            <p class="text-sm font-medium text-siakad-dark">{{ $row['santri']->nama_lengkap }}</p>
                            <p class="text-[10px] text-siakad-secondary">{{ $row['santri']->nis }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-center border-l text-sm text-siakad-dark" dir="rtl"
                            style="border-color: var(--border-color); font-family: 'Traditional Arabic', 'Arial', sans-serif;">
                            {{ $row['santri']->nama_arab ?? '—' }}
                        </td>
                        @foreach($data['mapelList'] as $mapel)
                        @php $nilai = $row['nilai'][$mapel->id] ?? null; @endphp
                        <td class="px-2 py-2.5 text-center border-l text-xs
                               {{ $nilai !== null && $data['kkmPerMapel'][$mapel->id] !== null && $nilai < $data['kkmPerMapel'][$mapel->id] ? 'text-red-600 font-semibold' : 'text-siakad-dark' }}"
                            style="border-color: var(--border-color);">
                            {{ $nilai !== null ? number_format($nilai, 1) : '—' }}
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center font-bold border-l" style="color: var(--siakad-primary); border-color: var(--border-color);">
                            {{ number_format($row['jumlah'], 1) }}
                        </td>
                        <td class="px-3 py-2.5 text-center text-siakad-dark">
                            {{ $row['rata_rata'] }}
                        </td>
                        <td class="px-3 py-2.5 text-center text-siakad-dark">
                            {{ $row['peringkat'] ?? '—' }}
                        </td>
                        @foreach(['sakit','izin','alpa'] as $s)
                        <td class="px-1 py-2.5 text-center text-xs border-l
                               {{ $s === 'alpa' && ($row['kehadiran'][$s] ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-siakad-secondary' }}"
                            style="border-color: var(--border-color);">
                            {{ $row['kehadiran'][$s] ?? 0 }}
                        </td>
                        @endforeach
                        @foreach(['akhlaq','kerajinan','kebersihan','kedisiplinan'] as $k)
                        <td class="px-1 py-2.5 text-center text-xs text-siakad-dark border-l" style="border-color: var(--border-color);">
                            {{ $row['kepribadian'][$k] }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 text-xs text-siakad-secondary border-t" style="border-color: var(--border-color);">
            S = Sakit · I = Izin · A = Tanpa Keterangan. Nilai berwarna merah di bawah KKM mapel tersebut.
        </div>
    </div>
    @endif
</x-app-layout>