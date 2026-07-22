{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/show.blade.php - revised       --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Presensi</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('guru.presensi.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                {{ $pertemuan->mataPelajaran->nama }}
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $pertemuan->kelas->nama }} ·
                {{ $pertemuan->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }} ·
                Pertemuan ke-{{ $pertemuan->pertemuan_ke }}
            </p>
        </div>
    </div>

    <div class="space-y-5 max-w-3xl">

        {{-- Info card --}}
        <div class="card-saas overflow-hidden">
            <div class="p-5" style="background: linear-gradient(135deg, var(--siakad-primary), var(--siakad-dark));">
                @if($pertemuan->topik)
                <p class="text-white/70 text-xs uppercase tracking-wide mb-1">Topik</p>
                <p class="font-semibold text-white">{{ $pertemuan->topik }}</p>
                @endif

                @if($pertemuan->materi)
                <p class="text-white/70 text-xs uppercase tracking-wide mb-1 mt-3">Materi yang Disampaikan</p>
                <p class="text-white/90 text-sm leading-relaxed">{{ $pertemuan->materi }}</p>
                @endif

                {{-- Rekap badges --}}
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach([
                    ['hadir', $rekap['hadir'], 'H'],
                    ['sakit', $rekap['sakit'], 'S'],
                    ['izin', $rekap['izin'], 'I'],
                    ['alpa', $rekap['alpa'], 'A'],
                    ] as [$label, $count, $kode])
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/15 rounded-xl">
                        <span class="text-xs font-bold text-white">{{ $kode }}</span>
                        <span class="text-sm font-bold text-white">{{ $count }}</span>
                        <span class="text-xs text-white/70 capitalize">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tabel presensi --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['No','Nama Santri','Status','Keterangan'] as $h)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($pertemuan->presensiKbm->sortBy('santri.nama_lengkap') as $i => $p)
                        <tr class="dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 text-xs text-siakad-secondary">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-5 py-3 font-medium text-siakad-dark">
                                {{ $p->santri->nama_lengkap }}
                            </td>
                            <td class="px-5 py-3">
                                <span @class([ 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold' , 'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800'=> $p->status === 'hadir',
                                    'bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800' => $p->status === 'sakit',
                                    'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 ring-1 ring-yellow-200 dark:ring-yellow-800' => $p->status === 'izin',
                                    'bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800' => $p->status === 'alpa',
                                    ])>
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-siakad-secondary">
                                {{ $p->keterangan ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>