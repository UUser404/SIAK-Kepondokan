{{-- ============================================================ --}}
{{-- resources/views/laporan/kesantrian.blade.php                --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Laporan Kesantrian</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Laporan Kesantrian</h2>
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

    <div class="grid md:grid-cols-2 gap-5 mb-6">

        {{-- Pelanggaran --}}
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full bg-red-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">
                    Pelanggaran — {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
                </h3>
            </div>
            <div class="grid grid-cols-3 gap-3 mb-5">
                @foreach(['ringan'=>'yellow','sedang'=>'orange','berat'=>'red'] as $t => $c)
                <div class="text-center p-3 rounded-xl bg-{{ $c }}-50 $c }}-900/20">
                    <p class="text-xl font-bold text-{{ $c }}-600 $c }}-400">
                        {{ $pelanggaranData[$t] }}
                    </p>
                    <p class="text-xs text-{{ $c }}-500 mt-0.5">{{ ucfirst($t) }}</p>
                </div>
                @endforeach
            </div>

            <p class="text-xs font-semibold text-siakad-secondary uppercase tracking-wide mb-2">
                Top 5 Poin Pelanggaran
            </p>
            <div class="space-y-2">
                @foreach($topPelanggaran as $i => $santri)
                <div class="flex items-center gap-3">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold
                             {{ $i === 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $i + 1 }}
                    </span>
                    <p class="flex-1 text-sm text-siakad-dark truncate">
                        {{ $santri->nama_lengkap }}
                    </p>
                    <span class="text-sm font-bold text-red-600">
                        {{ $santri->poin_total ?? 0 }} poin
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Prestasi & Asrama --}}
        <div class="space-y-4">
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-yellow-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">
                        Prestasi {{ $tahun }}
                    </h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 rounded-xl bg-blue-50">
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $prestasiData['total'] }}
                        </p>
                        <p class="text-xs text-blue-500">Total Prestasi</p>
                    </div>
                    <div class="text-center p-3 rounded-xl bg-yellow-50">
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ $prestasiData['nasional_plus'] }}
                        </p>
                        <p class="text-xs text-yellow-500">Nasional+</p>
                    </div>
                </div>
            </div>

            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Hunian Asrama</h3>
                </div>
                <div class="space-y-3">
                    @foreach($hunianAsrama as $asrama)
                    @php $persen = $asrama['kapasitas'] > 0 ? round(($asrama['penghuni']/$asrama['kapasitas'])*100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-siakad-dark">{{ $asrama['nama'] }}</span>
                            <span class="text-siakad-secondary">
                                {{ $asrama['penghuni'] }}/{{ $asrama['kapasitas'] }} ({{ $persen }}%)
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full"
                                style="width: {{ $persen }}%;
                                    background-color: {{ $persen >= 90 ? '#dc2626' : 'var(--siakad-primary)' }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</x-app-layout>