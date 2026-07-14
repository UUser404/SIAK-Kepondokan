{{-- ============================================================ --}}
{{-- resources/views/rapor/show.blade.php                        --}}
{{-- Preview rapor satu santri                                   --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Rapor — {{ $santri->nama_lengkap }}</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('kurikulum.rapor.index') }}"
                class="p-2 rounded-xl border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
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
        <a href="{{ route('kurikulum.rapor.cetak', $santri) }}" target="_blank"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
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
    </div>

    <div class="space-y-5 max-w-4xl">

        {{-- Summary --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach([
            ['Rata-rata Nilai', $rataRata, $rataRata >= 70 ? 'green' : 'red'],
            ['Mapel Tuntas', "{$totalTuntas}/{$totalMapel}", $totalTuntas === $totalMapel ? 'green' : 'yellow'],
            ['Predikat Umum', $rataRata >= 90 ? 'A' : ($rataRata >= 80 ? 'B' : ($rataRata >= 70 ? 'C' : 'D')), 'blue'],
            ] as [$label, $value, $color])
            <div class="card-saas p-5 text-center">
                <p class="text-2xl font-bold text-{{ $color }}-600 $color }}-400">
                    {{ $value }}
                </p>
                <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Tabel nilai --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Nilai Per Mata Pelajaran — {{ $ta?->nama_lengkap }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['#','Mata Pelajaran','UH','Tugas','Praktik','UTS','UAS','Nilai Akhir','Predikat','KKM','Status','H','S','I','A'] as $h)
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary whitespace-nowrap
                                   first:text-left first:px-5">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($nilaiAkhir as $i => $na)
                        @php $kehadiran = $kehadiranMapel[$na->mata_pelajaran_id] ?? null; @endphp
                        <tr class="{{ $na->tuntas ? '' : 'bg-red-50/30' }}
                              ">
                            <td class="px-5 py-3 text-xs text-siakad-secondary">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-5 py-3 font-medium text-siakad-dark whitespace-nowrap">
                                {{ $na->mataPelajaran->nama }}
                            </td>
                            @foreach(['nilai_uh','nilai_tugas','nilai_praktik','nilai_uts','nilai_uas'] as $field)
                            <td class="px-3 py-3 text-center text-siakad-secondary">
                                {{ $na->$field ? number_format($na->$field, 1) : '—' }}
                            </td>
                            @endforeach
                            <td class="px-3 py-3 text-center font-bold
                                   {{ $na->tuntas ? 'text-green-600' : 'text-red-600' }}">
                                {{ $na->nilai_akhir ? number_format($na->nilai_akhir, 1) : '—' }}
                            </td>
                            <td class="px-3 py-3 text-center font-semibold text-siakad-dark">
                                {{ $na->predikat ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-center text-siakad-secondary">
                                {{ $na->mataPelajaran->kkm }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $na->tuntas
                                   ? 'bg-green-100 text-green-700'
                                   : 'bg-red-100 text-red-700' }}">
                                    {{ $na->tuntas ? 'T' : 'BT' }}
                                </span>
                            </td>
                            {{-- Kehadiran --}}
                            @foreach(['hadir','sakit','izin','alpa'] as $s)
                            <td class="px-3 py-3 text-center text-xs
                                   {{ $s === 'alpa' && ($kehadiran[$s] ?? 0) > 0
                                      ? 'text-red-600 font-semibold'
                                      : 'text-siakad-secondary' }}">
                                {{ $kehadiran[$s] ?? 0 }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>