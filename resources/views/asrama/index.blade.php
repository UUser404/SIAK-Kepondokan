{{-- ============================================================ --}}
{{-- resources/views/asrama/index.blade.php                      --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Asrama</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Manajemen Asrama</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Kelola gedung asrama dan hunian santri
            </p>
        </div>
        <a href="{{ route('kesantrian.asrama.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Asrama
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['Total Kamar', $stats['total_kamar'], ''],
        ['Kapasitas Total',$stats['total_kapasitas'], ''],
        ['Terisi', $stats['total_penghuni'], ''],
        ['Tersedia', $stats['tersedia'], ''],
        ] as [$label, $value])
        <div class="card-saas p-5">
            <p class="text-2xl font-bold text-siakad-dark">{{ $value }}</p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Asrama grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($asramaList as $asrama)
        @php
        $totalKamar = $asrama->kamar->count();
        $totalPenghuni = $asrama->kamar->sum('penghuni_count');
        $totalKap = $asrama->kamar->sum('kapasitas');
        $persen = $totalKap > 0 ? round(($totalPenghuni / $totalKap) * 100) : 0;
        @endphp
        <div class="card-saas overflow-hidden hover:border-siakad-primary/30 transition">
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold
                                flex-shrink-0"
                            style="background-color: {{ $asrama->jenis === 'putra' ? 'var(--siakad-primary)' : '#7c3aed' }};">
                            {{ strtoupper(substr($asrama->nama, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-siakad-dark">{{ $asrama->nama }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $asrama->jenis === 'putra'
                               ? 'bg-blue-100 text-blue-700'
                               : 'bg-purple-100 text-purple-700' }}">
                                {{ ucfirst($asrama->jenis) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('kesantrian.asrama.edit', $asrama) }}"
                            class="p-2 rounded-lg text-siakad-secondary hover:bg-siakad-primary/10
                              hover:text-siakad-primary transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                     m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Pengurus --}}
                @if($asrama->pengurus)
                <p class="text-xs text-siakad-secondary mb-3">
                    Pengurus: <span class="font-medium text-siakad-dark">
                        {{ $asrama->pengurus }}
                    </span>
                </p>
                @endif

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-3 mb-4">
                    @foreach([
                    ['Kamar', $totalKamar],
                    ['Penghuni', $totalPenghuni],
                    ['Kapasitas', $totalKap],
                    ] as [$lbl, $val])
                    <div class="text-center p-2 rounded-xl bg-gray-50">
                        <p class="text-lg font-bold text-siakad-dark">{{ $val }}</p>
                        <p class="text-[10px] text-siakad-secondary">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Hunian bar --}}
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-siakad-secondary">Tingkat hunian</span>
                        <span class="font-semibold text-siakad-dark">{{ $persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all"
                            style="width: {{ $persen }}%;
                                background-color: {{ $persen >= 90 ? '#dc2626' : ($persen >= 70 ? '#d97706' : 'var(--siakad-primary)') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color);">
                <a href="{{ route('kesantrian.asrama.show', $asrama) }}"
                    class="block px-5 py-3 text-xs font-medium text-center transition-colors
                      text-siakad-secondary
                      hover:text-siakad-primary
                      hover:bg-siakad-primary/5">
                    Kelola Kamar →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 card-saas p-16 text-center">
            <p class="text-siakad-secondary text-sm">Belum ada data asrama</p>
        </div>
        @endforelse
    </div>
</x-app-layout>