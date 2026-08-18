{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/jurnal-show.blade.php          --}}
{{-- Detail JURNAL (beda dari presensi-kbm/show.blade.php) --     --}}
{{-- fokus ke topik/materi/catatan, kehadiran cuma ringkasan H/S/  --}}
{{-- I/A, detail nama cuma untuk yang TIDAK hadir.                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Jurnal</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('guru.jurnal.index') }}"
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
                @if($pertemuan->guru_id !== auth()->id())
                · <span class="italic">diinput oleh {{ $pertemuan->guru->name }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="space-y-5">

        {{-- Topik / Materi / Catatan -- ini fokus utama halaman jurnal,
             ditaruh paling atas & paling menonjol. --}}
        <div class="card-saas overflow-hidden">
            <div class="p-5" style="background: linear-gradient(135deg, var(--siakad-primary), var(--siakad-dark));">
                @if($pertemuan->topik)
                <p class="text-white/70 text-xs uppercase tracking-wide mb-1">Topik</p>
                <p class="font-semibold text-white text-lg">{{ $pertemuan->topik }}</p>
                @else
                <p class="text-white/70 text-sm italic">Topik belum diisi</p>
                @endif

                @if($pertemuan->materi)
                <p class="text-white/70 text-xs uppercase tracking-wide mb-1 mt-4">Materi yang Disampaikan</p>
                <p class="text-white/90 text-sm leading-relaxed">{{ $pertemuan->materi }}</p>
                @endif

                @if($pertemuan->catatan_guru)
                <p class="text-white/70 text-xs uppercase tracking-wide mb-1 mt-4">Catatan Guru</p>
                <p class="text-white/90 text-sm leading-relaxed">{{ $pertemuan->catatan_guru }}</p>
                @endif
            </div>
        </div>

        {{-- Ringkasan kehadiran -- CUMA angka, bukan tabel lengkap semua
             santri (itu bedanya dari presensi-kbm/show.blade.php). --}}
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Ringkasan Kehadiran</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                ['Hadir', $rekap['hadir'], 'green'],
                ['Sakit', $rekap['sakit'], 'blue'],
                ['Izin', $rekap['izin'], 'yellow'],
                ['Alpa', $rekap['alpa'], 'red'],
                ] as [$label, $count, $color])
                <div class="p-4 rounded-xl text-center border border-gray-100 dark:border-gray-700">
                    <p class="text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $count }}</p>
                    <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Detail ketidakhadiran -- CUMA santri yang sakit/izin/alpa yang
             disebut namanya. Santri yang hadir tidak perlu ditampilkan satu-
             satu di sini (itu "kondisi normal", sudah terwakili di angka
             ringkasan di atas). --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-red-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Detail Ketidakhadiran ({{ $tidakHadir->count() }})
                </h3>
            </div>

            @if($tidakHadir->isEmpty())
            <div class="px-5 py-8 text-center text-sm text-siakad-secondary">
                Semua santri hadir pada pertemuan ini.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Nama Santri','Status','Keterangan'] as $h)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($tidakHadir as $p)
                        <tr class="dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-siakad-dark">{{ $p->santri->nama_lengkap }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $p->santri->nis }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span @class([ 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold' , 'bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800'=> $p->status === 'sakit',
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
            @endif
        </div>

        {{-- Link ke detail presensi lengkap, buat yang butuh lihat semua
             santri (termasuk yang hadir) satu-satu. --}}
        <p class="text-sm text-siakad-secondary">
            Butuh lihat status semua santri (termasuk yang hadir)?
            <a href="{{ route('guru.presensi.show', $pertemuan) }}" class="font-medium underline" style="color: var(--siakad-primary);">
                Lihat detail presensi lengkap
            </a>
        </p>

    </div>
</x-app-layout>