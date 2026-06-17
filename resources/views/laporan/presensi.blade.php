{{-- ============================================================ --}}
{{-- resources/views/laporan/presensi.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Laporan Presensi</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Laporan Presensi</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
            </p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="bulan" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" @selected($bulan==$b)>
                    {{ \Carbon\Carbon::create()->month($b)->locale('id')->isoFormat('MMMM') }}
                </option>
                @endforeach
            </select>
            <select name="tahun" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                @foreach(range(now()->year, now()->year-2) as $y)
                <option value="{{ $y }}" @selected($tahun==$y)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Presensi KBM per kelas --}}
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Kehadiran KBM Per Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Kelas','Total Record','Hadir','Alpa','% Kehadiran'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($presensiKbmPerKelas as $data)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3 font-medium text-siakad-dark">{{ $data['kelas'] }}</td>
                        <td class="px-5 py-3 text-siakad-secondary">{{ $data['total'] }}</td>
                        <td class="px-5 py-3 font-semibold text-green-600">{{ $data['hadir'] }}</td>
                        <td class="px-5 py-3 font-semibold text-red-600">{{ $data['alpa'] }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                        style="width: {{ $data['persen'] }}%;
                                            background-color: {{ $data['persen'] >= 80 ? '#16a34a' : ($data['persen'] >= 60 ? 'var(--siakad-primary)' : '#dc2626') }}">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold {{ $data['persen'] >= 80 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $data['persen'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5">

        {{-- Presensi kegiatan pondok --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-purple-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Kehadiran Kegiatan Pondok
                </h3>
            </div>
            <div class="divide-y" style="border-color: var(--border-color);">
                @foreach($presensiKegiatan as $k)
                <div class="px-5 py-3">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="font-medium text-siakad-dark">{{ $k['nama'] }}</span>
                        <span class="text-siakad-secondary">
                            {{ $k['hari'] }} hari · {{ $k['persen'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full"
                            style="width: {{ $k['persen'] }}%;
                                background-color: {{ $k['persen'] >= 80 ? '#16a34a' : ($k['persen'] >= 60 ? 'var(--siakad-primary)' : '#dc2626') }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top 10 santri alpa terbanyak --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-red-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Santri Alpa Terbanyak (KBM)
                </h3>
            </div>
            <div class="divide-y" style="border-color: var(--border-color);">
                @forelse($santriAlpa as $i => $record)
                <div class="px-5 py-3 flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold
                             {{ $i === 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $i + 1 }}
                    </span>
                    <p class="flex-1 text-sm text-siakad-dark truncate">
                        {{ $record->santri?->nama_lengkap ?? '—' }}
                    </p>
                    <span class="text-sm font-bold text-red-600">
                        {{ $record->total_alpa }}x
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-siakad-secondary">
                    Tidak ada data alpa bulan ini
                </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>