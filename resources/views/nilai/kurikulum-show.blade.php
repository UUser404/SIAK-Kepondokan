{{-- ============================================================ --}}
{{-- resources/views/nilai/kurikulum-show.blade.php              --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Nilai</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kurikulum.nilai.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark">Detail Nilai</h2>
    </div>

    {{-- Filter kelas & mapel --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" @selected($kelas?->id == $k->id)>
                        {{ $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-siakad-secondary mb-1">Mata Pelajaran</label>
                <select name="mapel_id" onchange="this.form.submit()"
                    class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark
                           focus:ring-2 outline-none transition">
                    @foreach($mapelList as $m)
                    <option value="{{ $m->id }}" @selected($mataPelajaran?->id == $m->id)>
                        {{ $m->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($rekap && $statistik)

    {{-- Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @foreach([
        ['Rata-rata', $statistik['rata_rata'], ''],
        ['Tertinggi', $statistik['tertinggi'], ''],
        ['Terendah', $statistik['terendah'], ''],
        ['Tuntas', $statistik['jumlah_tuntas'], ''],
        ['% Tuntas', $statistik['persen_tuntas'].'%',''],
        ] as [$label, $value])
        <div class="card-saas p-4 text-center">
            <p class="text-xl font-bold text-siakad-dark">{{ $value }}</p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Distribusi predikat --}}
    <div class="card-saas p-5 mb-6">
        <p class="text-xs font-semibold text-siakad-secondary uppercase tracking-wide mb-4">
            Distribusi Predikat
        </p>
        <div class="grid grid-cols-4 gap-3">
            @foreach(['A'=>'green','B'=>'blue','C'=>'yellow','D'=>'red'] as $predikat => $color)
            @php $count = $statistik['distribusi'][$predikat] ?? 0; @endphp
            <div class="text-center p-3 rounded-xl bg-{{ $color }}-50 $color }}-900/20
                    border border-{{ $color }}-100 $color }}-800">
                <p class="text-2xl font-bold text-{{ $color }}-600 $color }}-400">
                    {{ $count }}
                </p>
                <p class="text-xs font-semibold text-{{ $color }}-700 $color }}-300 mt-0.5">
                    Predikat {{ $predikat }}
                </p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Finalisasi nilai --}}
    <div class="mb-6">
        <form method="POST" action="{{ route('kurikulum.nilai.finalize') }}" class="inline">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">
            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
                       text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'"
                onclick="return confirm('Kalkulasi ulang nilai akhir semua santri di kelas ini?')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0
                         0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Kalkulasi Nilai Akhir
            </button>
        </form>
    </div>

    {{-- Tabel rekap --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">
                {{ $kelas->nama }} — {{ $mataPelajaran->nama }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary sticky left-0 z-10"
                            style="background-color: rgba(35,76,106,0.04);">Santri</th>
                        @foreach($rekap['komponen'] as $k)
                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary min-w-[70px]">
                            {{ $k->kode }}
                        </th>
                        @endforeach
                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               min-w-[80px]" style="color: var(--siakad-primary);">Akhir</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($rekap['rows'] as $row)
                    @php $na = $row['nilai_akhir']; @endphp
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3 sticky left-0 bg-white z-10">
                            <p class="font-medium text-siakad-dark text-xs">
                                {{ $row['santri']->nama_lengkap }}
                            </p>
                        </td>
                        @foreach($rekap['komponen'] as $k)
                        <td class="px-3 py-3 text-center text-siakad-dark">
                            {{ $row['komponen'][$k->kode] !== null
                           ? number_format($row['komponen'][$k->kode], 1)
                           : '—' }}
                        </td>
                        @endforeach
                        <td class="px-3 py-3 text-center font-bold">
                            @if($na)
                            <span class="{{ $na->tuntas
                            ? 'text-green-600'
                            : 'text-red-600' }}">
                                {{ $na->nilai_akhir }}
                            </span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($na)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $na->tuntas
                               ? 'bg-green-100 text-green-700'
                               : 'bg-red-100 text-red-700' }}">
                                {{ $na->predikat }} · {{ $na->tuntas ? 'Tuntas' : 'Belum' }}
                            </span>
                            @else
                            <span class="text-gray-300 text-xs">Belum diisi</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @endif
</x-app-layout>