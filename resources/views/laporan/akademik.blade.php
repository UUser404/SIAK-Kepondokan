{{-- ============================================================ --}}
{{-- resources/views/laporan/akademik.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Laporan Akademik</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Laporan Akademik</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $ta?->nama_lengkap ?? 'Belum ada tahun ajaran aktif' }}
            </p>
        </div>
    </div>

    {{-- Summary KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
        ['Total Santri', $summary['total_santri'], ''],
        ['Tenaga Pendidik', $summary['total_pendidik'], ''],
        ['Total Kelas', $summary['total_kelas'], ''],
        ['Rata-rata Nilai', $summary['rata_nilai_global'], $summary['rata_nilai_global'] >= 70 ? 'green' : 'red'],
        ['% Tuntas', $summary['persen_tuntas_global'].'%', $summary['persen_tuntas_global'] >= 75 ? 'green' : 'yellow'],
        ] as [$label, $value, $color])
        <div class="card-saas p-5 text-center">
            <p class="text-2xl font-bold
                  {{ $color === 'green' ? 'text-green-600'
                    : ($color === 'red' ? 'text-red-600'
                    : ($color === 'yellow' ? 'text-yellow-600'
                    : 'text-siakad-dark')) }}">
                {{ $value }}
            </p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Per Kelas --}}
    <div class="card-saas overflow-hidden mb-6">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Rekap Per Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Kelas','Santri','Wali Kelas','Rata-rata','% Tuntas','A','B','C','D'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($kelasList as $kelas)
                    @php $d = $nilaiPerKelas[$kelas->id] ?? []; @endphp
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 font-medium text-siakad-dark">
                            {{ $kelas->nama }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary">
                            {{ $kelas->jumlah_santri }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                            {{ $kelas->waliKelas?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold
                               {{ ($d['rata_rata'] ?? 0) >= 70
                                  ? 'text-green-600'
                                  : 'text-red-600' }}">
                            {{ $d['rata_rata'] ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                        style="width: {{ $d['persen_tuntas'] ?? 0 }}%;
                                            background-color: {{ ($d['persen_tuntas'] ?? 0) >= 75 ? '#16a34a' : 'var(--siakad-primary)' }}">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-siakad-dark">
                                    {{ $d['persen_tuntas'] ?? 0 }}%
                                </span>
                            </div>
                        </td>
                        @foreach(['A','B','C','D'] as $p)
                        <td class="px-5 py-3.5 text-center text-xs font-medium
                               {{ $p === 'A' ? 'text-green-600' : ($p === 'D' ? 'text-red-600' : 'text-siakad-secondary') }}">
                            {{ $d['distribusi'][$p] ?? 0 }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per Mapel --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Rekap Per Mata Pelajaran</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Mata Pelajaran','KKM','Rata-rata','% Tuntas'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($nilaiPerMapel as $data)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3 font-medium text-siakad-dark">
                            {{ $data['nama'] }}
                        </td>
                        <td class="px-5 py-3 text-siakad-secondary">
                            {{ $data['kkm'] }}
                        </td>
                        <td class="px-5 py-3 font-semibold
                               {{ $data['rata_rata'] >= $data['kkm']
                                  ? 'text-green-600'
                                  : 'text-red-600' }}">
                            {{ $data['rata_rata'] }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full"
                                        style="width: {{ $data['persen_tuntas'] }}%;
                                            background-color: {{ $data['persen_tuntas'] >= 75 ? '#16a34a' : ($data['persen_tuntas'] >= 50 ? 'var(--siakad-primary)' : '#dc2626') }}">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-siakad-dark">
                                    {{ $data['persen_tuntas'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>