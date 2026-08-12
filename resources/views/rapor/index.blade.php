{{-- ============================================================ --}}
{{-- resources/views/rapor/index.blade.php                       --}}
{{-- View ini dipakai bersama oleh 2 grup rute yang beda role      --}}
{{-- (kurikulum.rapor.* untuk Wakil Kurikulum/Sysadmin, dan        --}}
{{-- wali-kelas.rapor.* untuk guru yang jadi wali kelas) tapi       --}}
{{-- dilayani controller yang sama (Kurikulum\RaporController).    --}}
{{-- Route generate HARUS dinamis sesuai grup yang sedang aktif -- --}}
{{-- kalau di-hardcode ke 'kurikulum.rapor.*', wali kelas kena 403 --}}
{{-- karena grup rute kurikulum.* dibatasi role wakil_kurikulum|   --}}
{{-- sysadmin lewat middleware, walau RaporController sendiri      --}}
{{-- sudah punya authorizeSantri() yang benar mengizinkan wali     --}}
{{-- kelas untuk kelasnya sendiri.                                 --}}
{{-- ============================================================ --}}
@php
$routePrefix = request()->routeIs('wali-kelas.*') ? 'wali-kelas' : 'kurikulum';
@endphp
<x-app-layout>
    <x-slot name="header">Rapor</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Rapor Santri</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Lihat dan cetak rapor per santri
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

    {{-- Filter kelas --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id')==$k->id)>
                        {{ $k->nama }} ({{ $k->jumlah_santri }} santri)
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($kelas && $santriList)

    {{-- Info kelas --}}
    <div class="card-saas p-4 mb-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold
                text-lg flex-shrink-0"
            style="background-color: var(--siakad-primary);">
            {{ strtoupper(substr($kelas->nama, 0, 2)) }}
        </div>
        <div class="flex-1">
            <p class="font-semibold text-siakad-dark">{{ $kelas->nama }}</p>
            <p class="text-xs text-siakad-secondary">
                {{ $kelas->tingkatan->nama }} · Wali Kelas: {{ $kelas->waliKelas?->name ?? '-' }}
            </p>
        </div>
        <p class="text-sm text-siakad-secondary">
            {{ $santriList->count() }} santri
        </p>
    </div>

    {{-- Tabel santri --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Daftar Santri</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['#','Nama Santri','Mapel Diisi','Rata-rata','Tuntas','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($santriList as $i => $santri)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                            {{ $i + 1 }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center
                                        text-xs font-bold text-white flex-shrink-0"
                                    style="background-color: var(--siakad-primary);">
                                    {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-siakad-dark">
                                        {{ $santri->nama_lengkap }}
                                    </p>
                                    <p class="text-xs text-siakad-secondary">
                                        {{ $santri->nis }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary">
                            {{ $santri->total_mapel }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-semibold
                            {{ $santri->rata_nilai >= 80
                               ? 'text-green-600'
                               : ($santri->rata_nilai >= 70
                                  ? 'text-yellow-600'
                                  : 'text-red-600') }}">
                                {{ $santri->rata_nilai ?: '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($santri->total_mapel > 0)
                            <span class="text-sm font-medium text-siakad-dark">
                                {{ $santri->mapel_tuntas }}/{{ $santri->total_mapel }}
                            </span>
                            @else
                            <span class="text-siakad-secondary text-xs">Belum ada nilai</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route($routePrefix.".rapor.show", $santri) }}"
                                    class="px-3 py-1.5 text-xs font-medium rounded-xl border transition
                                      hover:bg-siakad-primary/5"
                                    style="border-color: var(--siakad-primary); color: var(--siakad-primary);">
                                    Lihat
                                </a>
                                <a href="{{ route($routePrefix.".rapor.cetak", $santri) }}"
                                    target="_blank"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-xl text-white transition
                                      hover:-translate-y-0.5"
                                    style="background-color: var(--siakad-primary);"
                                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                                    PDF
                                </a>
                                <a href="{{ route($routePrefix.".rapor.cetak-arab", $santri) }}"
                                    target="_blank"
                                    class="px-3 py-1.5 text-xs font-medium rounded-xl border transition
                                      hover:bg-gray-50"
                                    style="border-color: var(--border-color); color: var(--siakad-secondary);"
                                    title="Cetak Rapor Arab">
                                    PDF Arab
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    <div class="card-saas p-16 text-center">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center"
            style="background-color: rgba(35,76,106,0.08);">
            <svg class="w-8 h-8 text-siakad-secondary"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                     00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <p class="text-siakad-secondary text-sm">
            Pilih kelas di atas untuk melihat daftar santri
        </p>
    </div>
    @endif
</x-app-layout>