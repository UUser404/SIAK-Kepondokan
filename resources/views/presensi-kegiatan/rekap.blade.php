{{-- ============================================================ --}}
{{-- resources/views/presensi-kegiatan/rekap.blade.php - revised --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Rekap Presensi Kegiatan</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Rekap Presensi Kegiatan</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Rekap kehadiran santri per kegiatan per bulan
            </p>
        </div>
        <a href="{{ route('kesantrian.presensi.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border
              border-gray-200 text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            ← Kembali
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Kegiatan</label>
                <select name="kegiatan_id" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    @foreach($kegiatanList as $k)
                    <option value="{{ $k->id }}" @selected(request('kegiatan_id')==$k->id)>
                        {{ $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
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
                    @foreach(range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" @selected($tahun==$y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @isset($rows)
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center justify-between flex-wrap gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="flex items-center gap-2">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <div>
                    <h3 class="font-semibold text-sm text-siakad-dark">
                        {{ $kegiatan->nama }}
                    </h3>
                    <p class="text-xs text-siakad-secondary">
                        {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }}
                        {{ $tahun }} · {{ $tanggalList->count() }} hari tercatat
                    </p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-siakad-secondary
                               uppercase tracking-wide sticky left-0 z-10 min-w-[160px]"
                            style="background-color: rgba(35,76,106,0.04);">
                            Santri
                        </th>
                        @foreach($tanggalList as $tgl)
                        <th class="px-2 py-2.5 text-center font-semibold text-siakad-secondary
                               min-w-[32px]">
                            {{ \Carbon\Carbon::parse($tgl)->format('d') }}
                        </th>
                        @endforeach
                        <th class="px-3 py-2.5 text-center font-semibold text-green-600
                               bg-green-50">H</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-blue-600
                               bg-blue-50">S</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-yellow-600
                               bg-yellow-50">I</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-red-600
                               bg-red-50">A</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-siakad-secondary
                              ">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($rows as $row)
                    <tr class="dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2.5 font-medium text-siakad-dark
                               sticky left-0 bg-white z-10">
                            {{ $row['santri']->nama_lengkap }}
                        </td>
                        @foreach($row['detail'] as $presensi)
                        <td class="px-2 py-2.5 text-center">
                            @if($presensi)
                            <span @class([ 'inline-flex w-5 h-5 rounded-md items-center justify-center font-bold mx-auto' , 'bg-green-100 text-green-700'=> $presensi->status === 'hadir',
                                'bg-blue-100 text-blue-700' => $presensi->status === 'sakit',
                                'bg-yellow-100 text-yellow-700' => $presensi->status === 'izin',
                                'bg-red-100 text-red-700' => $presensi->status === 'alpa',
                                ])>
                                {{ strtoupper(substr($presensi->status, 0, 1)) }}
                            </span>
                            @else
                            <span class="text-gray-200">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center font-semibold text-green-600
                               bg-green-50/50">{{ $row['hadir'] }}</td>
                        <td class="px-3 py-2.5 text-center text-blue-600
                               bg-blue-50/50">{{ $row['sakit'] }}</td>
                        <td class="px-3 py-2.5 text-center text-yellow-600
                               bg-yellow-50/50">{{ $row['izin'] }}</td>
                        <td class="px-3 py-2.5 text-center text-red-600
                               bg-red-50/50">{{ $row['alpa'] }}</td>
                        <td class="px-3 py-2.5 text-center">
                            <span @class([ 'font-semibold' , 'text-green-600'=> $row['persen'] >= 80,
                                'text-yellow-600'=> $row['persen'] >= 60 && $row['persen'] < 80, 'text-red-600'=> $row['persen'] < 60,
                                        ])>
                                        {{ $row['persen'] }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endisset
</x-app-layout>