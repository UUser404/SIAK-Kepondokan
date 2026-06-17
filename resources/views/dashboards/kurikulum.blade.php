{{-- ============================================================ --}}
{{-- resources/views/dashboards/kurikulum.blade.php              --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Dashboard Kurikulum</x-slot>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            Dashboard Kurikulum 📚
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Kelola kelas, jadwal, dan penilaian akademik santri
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['value'=>$totalKelas, 'label'=>'Total Kelas'],
        ['value'=>$totalSantri, 'label'=>'Total Santri'],
        ['value'=>$kelasBelumNilai, 'label'=>'Kelas Belum Nilai'],
        ['value'=>$pertemuanHariIni, 'label'=>'Pertemuan Hari Ini'],
        ] as $s)
        <div class="card-saas p-5">
            <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $s['value'] }}</p>
            <p class="text-xs mt-0.5" style="color: var(--siakad-secondary);">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="card-saas overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between"
            style="border-bottom: 1px solid var(--border-color);">
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Daftar Kelas Aktif</h3>
            <a href="{{ route('kurikulum.kelas.index') }}" class="text-xs font-medium"
                style="color: var(--siakad-primary);">Kelola kelas →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Kelas','Tingkatan','Wali Kelas','Santri'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide"
                            style="color: var(--siakad-secondary);">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($rekapKelas as $kelas)
                    <tr>
                        <td class="px-5 py-3 font-medium" style="color: var(--siakad-dark);">{{ $kelas->nama }}</td>
                        <td class="px-5 py-3" style="color: var(--siakad-secondary);">{{ $kelas->tingkatan->nama }}</td>
                        <td class="px-5 py-3" style="color: var(--siakad-secondary);">{{ $kelas->waliKelas?->name ?? '-' }}</td>
                        <td class="px-5 py-3 font-semibold" style="color: var(--siakad-dark);">{{ $kelas->jumlah_santri }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm"
                            style="color: var(--siakad-secondary);">Belum ada kelas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>