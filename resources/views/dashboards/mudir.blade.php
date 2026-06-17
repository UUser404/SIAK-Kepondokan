{{-- ============================================================ --}}
{{-- resources/views/dashboards/mudir.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Dashboard Eksekutif</x-slot>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            Dashboard Eksekutif 📊
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Ringkasan kondisi pondok secara menyeluruh
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['value'=>$totalSantri, 'label'=>'Total Santri', 'sub'=>'aktif'],
        ['value'=>$rataTingkatKehadiran.'%', 'label'=>'Kehadiran KBM', 'sub'=>'rata-rata bulan ini'],
        ['value'=>$rataRataNilai, 'label'=>'Rata-rata Nilai', 'sub'=>'seluruh mapel'],
        ['value'=>$pelanggaranAktif, 'label'=>'Pelanggaran Aktif', 'sub'=>'bulan ini'],
        ] as $kpi)
        <div class="card-saas p-5">
            <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $kpi['value'] }}</p>
            <p class="text-xs font-medium mt-0.5" style="color: var(--siakad-secondary);">{{ $kpi['label'] }}</p>
            <p class="text-[11px] mt-0.5 text-gray-400">{{ $kpi['sub'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 gap-5">
        <div class="card-saas p-5">
            <h3 class="font-semibold text-sm mb-4" style="color: var(--siakad-dark);">Ringkasan Akademik</h3>
            <dl class="space-y-2.5 text-sm">
                @foreach([
                ['Kelas aktif', $totalKelas],
                ['Tenaga pendidik', $totalPendidik],
                ['Mata pelajaran', $totalMapel],
                ] as [$label, $value])
                <div class="flex justify-between items-center">
                    <dt style="color: var(--siakad-secondary);">{{ $label }}</dt>
                    <dd class="font-semibold" style="color: var(--siakad-dark);">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
        <div class="card-saas p-5">
            <h3 class="font-semibold text-sm mb-4" style="color: var(--siakad-dark);">Ringkasan Kesantrian</h3>
            <dl class="space-y-2.5 text-sm">
                @foreach([
                ['Hunian kamar', $totalHunian . '/' . $totalKapasitasKamar],
                ['Santri berprestasi', $totalPrestasi],
                ['Pendaftar PPDB', $totalPpdb],
                ] as [$label, $value])
                <div class="flex justify-between items-center">
                    <dt style="color: var(--siakad-secondary);">{{ $label }}</dt>
                    <dd class="font-semibold" style="color: var(--siakad-dark);">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app-layout>