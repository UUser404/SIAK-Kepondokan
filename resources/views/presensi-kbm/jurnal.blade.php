{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/jurnal.blade.php - revised     --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Jurnal Mengajar</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark">Jurnal Mengajar</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Rekap aktivitas mengajar per bulan
        </p>
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Bulan</label>
                <select name="bulan" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" @selected($bulan==$b)>
                        {{ \Carbon\Carbon::create()->month($b)->locale('id')->isoFormat('MMMM') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Tahun</label>
                <select name="tahun" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    @foreach(range(now()->year, now()->year - 2) as $y)
                    <option value="{{ $y }}" @selected($tahun==$y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach([
        ['Total Pertemuan', $totalPertemuan, 'siakad-primary'],
        ['Santri Hadir', $totalSantriHadir, 'green'],
        ['Santri Alpa', $totalSantriAlpa, 'red'],
        ] as [$label, $value, $color])
        <div class="card-saas p-5 text-center">
            <p class="text-2xl font-bold text-{{ $color }}-600 $color }}-400"
                @if($color==='siakad-primary' ) style="color: var(--siakad-primary);" @endif>
                {{ $value }}
            </p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Rekap per kelas --}}
    @if($rekapPerKelas->isNotEmpty())
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Rekap per Kelas</h3>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @foreach($rekapPerKelas as $rekap)
            <div class="px-5 py-3.5 flex items-center justify-between">
                <div>
                    <p class="font-medium text-siakad-dark">
                        {{ $rekap['mata_pelajaran']->nama }}
                    </p>
                    <p class="text-xs text-siakad-secondary">
                        {{ $rekap['kelas']->nama }} · {{ $rekap['jumlah_pertemuan'] }} pertemuan
                    </p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-siakad-dark">
                        {{ $rekap['rata_kehadiran'] }}%
                    </p>
                    <p class="text-xs text-siakad-secondary">rata kehadiran</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabel pertemuan --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">
                Riwayat Pertemuan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Tanggal','Mapel / Kelas','Topik','H','S','I','A',''] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($pertemuanList as $p)
                    <tr class="dark:hover:bg-gray-700/50">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <p class="text-siakad-dark">
                                {{ $p->tanggal->locale('id')->isoFormat('ddd, D MMM') }}
                            </p>
                            <p class="text-xs text-siakad-secondary">
                                Ke-{{ $p->pertemuan_ke }}
                            </p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-medium text-siakad-dark">
                                {{ $p->mataPelajaran->nama }}
                            </p>
                            <p class="text-xs text-siakad-secondary">
                                {{ $p->kelas->nama }}
                            </p>
                        </td>
                        <td class="px-5 py-3 text-siakad-secondary max-w-[160px] truncate">
                            {{ $p->topik ?? '—' }}
                        </td>
                        @foreach(['hadir'=>'green','sakit'=>'blue','izin'=>'yellow','alpa'=>'red'] as $s => $c)
                        <td class="px-5 py-3 text-center font-semibold
                               text-{{ $c }}-600 $c }}-400">
                            {{ $p->presensiKbm->where('status', $s)->count() }}
                        </td>
                        @endforeach
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('guru.presensi.show', $p) }}"
                                class="text-xs font-medium transition-colors"
                                style="color: var(--siakad-primary);">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada pertemuan bulan ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>