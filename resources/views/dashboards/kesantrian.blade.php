{{-- ============================================================ --}}
{{-- resources/views/dashboards/kesantrian.blade.php             --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Dashboard Kesantrian</x-slot>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            Dashboard Kesantrian 🏠
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Pantau kondisi santri dan kegiatan harian pondok
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['value'=>$totalSantri, 'label'=>'Total Santri'],
        ['value'=>$presensiHariIni.'%', 'label'=>'Kehadiran Hari Ini'],
        ['value'=>$pelanggaranAktif, 'label'=>'Pelanggaran Aktif'],
        ['value'=>$kamarTersedia, 'label'=>'Kamar Tersedia'],
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
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Pelanggaran Terbaru</h3>
            <a href="{{ route('kesantrian.pelanggaran.index') }}" class="text-xs font-medium"
                style="color: var(--siakad-primary);">Lihat semua →</a>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @forelse($pelanggaranTerbaru as $p)
            <div class="px-5 py-3.5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color: var(--siakad-dark);">
                        {{ $p->santri->nama_lengkap }}
                    </p>
                    <p class="text-xs" style="color: var(--siakad-secondary);">
                        {{ $p->kategori->nama }} · {{ $p->tanggal->diffForHumans() }}
                    </p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($p->kategori->tingkat === 'ringan') bg-yellow-100 text-yellow-700
                @elseif($p->kategori->tingkat === 'sedang') bg-orange-100 text-orange-700
                @else bg-red-100 text-red-700 @endif">
                    {{ ucfirst($p->kategori->tingkat) }}
                </span>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-sm" style="color: var(--siakad-secondary);">
                Tidak ada pelanggaran aktif
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>