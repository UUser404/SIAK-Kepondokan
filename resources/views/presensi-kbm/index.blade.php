{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/index.blade.php - revised     --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Presensi KBM</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Presensi KBM</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Input dan pantau kehadiran santri di kelas
            </p>
        </div>
    </div>

    {{-- Navigasi minggu --}}
    <div class="card-saas p-4 mb-6">
        <div class="flex items-center justify-between">
            <a href="?minggu={{ \Carbon\Carbon::parse($minggu)->subWeek()->format('Y-m-d') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-xl border
                  border-gray-200 text-siakad-secondary
                  hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Minggu lalu
            </a>

            <div class="text-center">
                <p class="font-semibold text-siakad-dark text-sm">
                    {{ $mulai->locale('id')->isoFormat('D MMMM') }} —
                    {{ $selesai->locale('id')->isoFormat('D MMMM Y') }}
                </p>
                @if($mulai->isCurrentWeek())
                <span class="text-xs font-medium px-2 py-0.5 rounded-full text-white"
                    style="background-color: var(--siakad-primary);">
                    Minggu ini
                </span>
                @endif
            </div>

            @if(!$mulai->isCurrentWeek())
            <a href="?minggu={{ \Carbon\Carbon::parse($minggu)->addWeek()->format('Y-m-d') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-xl border
                  border-gray-200 text-siakad-secondary
                  hover:bg-gray-50 transition">
                Minggu depan
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @else
            <div class="w-32"></div>
            @endif
        </div>
    </div>

    {{-- Jadwal minggu ini --}}
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Jadwal Minggu Ini</h3>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @forelse($jadwalMingguIni as $item)
            @php
            $jadwal = $item['jadwal'];
            $tgl = $item['tanggal'];
            $sudah = $item['sudah_presensi'];
            $lewat = $tgl->isPast() || $tgl->isToday();
            @endphp
            <div class="px-5 py-3.5 flex items-center justify-between
                    {{ !$lewat ? 'opacity-40' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="w-12 text-center flex-shrink-0">
                        <p class="text-[10px] uppercase tracking-wide text-siakad-secondary">
                            {{ $tgl->locale('id')->isoFormat('ddd') }}
                        </p>
                        <p class="text-xl font-bold text-siakad-dark leading-tight">
                            {{ $tgl->format('d') }}
                        </p>
                    </div>
                    <div class="w-px h-10 bg-gray-200"></div>
                    <div>
                        <p class="font-medium text-siakad-dark">
                            {{ $jadwal->mataPelajaran->nama }}
                        </p>
                        <p class="text-xs text-siakad-secondary">
                            {{ $jadwal->kelas->nama }} ·
                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            @if($jadwal->ruangan) · {{ $jadwal->ruangan }} @endif
                        </p>
                    </div>
                </div>

                <div>
                    @if($sudah)
                    <a href="{{ route('guru.presensi.show', $item['pertemuan']->id) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                              rounded-xl bg-green-100 text-green-700
                              hover:bg-green-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Sudah · Lihat
                    </a>
                    @elseif($lewat)
                    <a href="{{ route('guru.presensi.create', $jadwal->id) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                              rounded-xl text-white transition hover:-translate-y-0.5"
                        style="background-color: var(--siakad-primary);"
                        onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                        onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                        Input Presensi
                    </a>
                    @else
                    <span class="text-xs text-siakad-secondary">Belum waktunya</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-siakad-secondary">
                Tidak ada jadwal minggu ini
            </div>
            @endforelse
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center justify-between"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="flex items-center gap-2">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Riwayat Pertemuan</h3>
            </div>
            <a href="{{ route('guru.jurnal.index') }}"
                class="text-xs font-medium transition-colors" style="color: var(--siakad-primary);">
                Lihat jurnal →
            </a>
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
                    @forelse($riwayat as $p)
                    <tr class="dark:hover:bg-gray-700/50">
                        <td class="px-5 py-3">
                            <p class="text-siakad-dark">
                                {{ $p->tanggal->locale('id')->isoFormat('ddd, D MMM Y') }}
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
                            {{ $p->topik ?? '-' }}
                        </td>
                        @foreach(['hadir'=>'green','sakit'=>'blue','izin'=>'yellow','alpa'=>'red'] as $s => $c)
                        <td class="px-5 py-3 text-center font-semibold text-{{ $c }}-600 $c }}-400">
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
                        <td colspan="8" class="px-5 py-10 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada riwayat pertemuan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>