{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/index.blade.php                --}}
{{-- Diganti dari basis jadwal (hari/jam) ke basis Penugasan      --}}
{{-- Mengajar (guru pilih kelas+mapel, tanggal diisi manual)      --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Presensi KBM</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark">Presensi KBM</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Pilih kelas & mata pelajaran yang Anda ampu untuk input presensi
        </p>
    </div>

    {{-- Kelas & mapel yang diampu --}}
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Kelas & Mata Pelajaran yang Diampu</h3>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @forelse($penugasanList as $penugasan)
            <div class="px-5 py-3.5 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background-color: rgba(35,76,106,0.1);">
                        <svg class="w-5 h-5" style="color: var(--siakad-primary);"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-siakad-dark">{{ $penugasan->mataPelajaran->nama }}</p>
                        <p class="text-xs text-siakad-secondary">
                            {{ $penugasan->kelas->nama }}
                            @if($penugasan->pertemuan_terakhir)
                                · Terakhir diinput {{ $penugasan->pertemuan_terakhir->tanggal->locale('id')->isoFormat('D MMM Y') }}
                            @else
                                · Belum pernah diinput
                            @endif
                        </p>
                    </div>
                </div>

                <a href="{{ route('guru.presensi.create', $penugasan->id) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                          rounded-xl text-white transition hover:-translate-y-0.5"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Input Presensi
                </a>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-siakad-secondary">
                Belum ada penugasan mengajar dari Kurikulum. Hubungi Wakil Kurikulum untuk pengaturan kelas & mapel Anda.
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
