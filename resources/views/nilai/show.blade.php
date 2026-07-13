{{-- ============================================================ --}}
{{-- resources/views/nilai/show.blade.php                        --}}
{{-- Spreadsheet-style input nilai — mendukung banyak input per   --}}
{{-- komponen (UH x2, Praktik x2, Tugas x4, dst sesuai maks_input) --}}
{{-- Nilai akhir komponen = rata-rata dari slot yang TERISI saja   --}}
{{-- (slot kosong dilewati, TIDAK dianggap 0)                      --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Input Nilai — {{ $mataPelajaran->nama }}</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('guru.nilai.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                {{ $mataPelajaran->nama }}
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $kelas->nama }} · KKM: {{ $mataPelajaran->kkm }}
            </p>
        </div>
    </div>

    {{-- Info bobot komponen --}}
    <div class="card-saas p-4 mb-6">
        <p class="text-xs font-semibold text-siakad-secondary uppercase tracking-wide mb-3">
            Bobot Komponen Penilaian
        </p>
        <div class="flex flex-wrap gap-3">
            @foreach($komponen as $k)
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl"
                style="background-color: rgba(35,76,106,0.08);">
                <span class="text-sm font-bold" style="color: var(--siakad-primary);">{{ $k->kode }}</span>
                <span class="text-xs text-siakad-secondary">{{ $k->nama }}</span>
                <span class="text-xs font-semibold text-siakad-dark">
                    {{ $k->bobot }}%
                </span>
                @if($k->maks_input > 1)
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white text-siakad-secondary">
                    maks {{ $k->maks_input }}x
                </span>
                @endif
            </div>
            @endforeach
        </div>
        <p class="text-[11px] text-siakad-secondary mt-3">
            Komponen dengan input lebih dari 1x (misalnya Tugas) boleh diisi sebagian saja —
            nilai akhir komponen dihitung dari rata-rata slot yang <b>terisi saja</b>, slot kosong dilewati (bukan dianggap 0).
        </p>
    </div>

    <form method="POST" action="{{ route('guru.nilai.bulk') }}" id="form-nilai">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        <input type="hidden" name="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">
        <input type="hidden" name="tahun_ajaran_id" value="{{ $ta?->id }}">

        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center justify-between flex-wrap gap-3"
                style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <div>
                        <h3 class="font-semibold text-sm text-siakad-dark">
                            Daftar Nilai Santri
                        </h3>
                        <p class="text-xs text-siakad-secondary">
                            {{ $santriList->count() }} santri
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-1.5 text-xs font-semibold rounded-xl text-white transition
                           hover:-translate-y-0.5"
                        style="background-color: var(--siakad-primary);">
                        Simpan Semua
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            <th rowspan="2" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary sticky left-0 z-10 min-w-[200px] align-bottom"
                                style="background-color: rgba(35,76,106,0.04);">
                                Santri
                            </th>
                            @foreach($komponen as $k)
                            <th colspan="{{ $k->maks_input }}" class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary border-l" style="border-color: var(--border-color);">
                                {{ $k->kode }}
                                <span class="block text-[10px] font-normal normal-case opacity-70">
                                    {{ $k->bobot }}%
                                </span>
                            </th>
                            @endforeach
                            <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               min-w-[80px] align-bottom border-l" style="color: var(--siakad-primary); border-color: var(--border-color);">
                                Akhir
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary min-w-[60px] align-bottom">
                                Status
                            </th>
                        </tr>
                        <tr>
                            @foreach($komponen as $k)
                                @for($slot = 1; $slot <= $k->maks_input; $slot++)
                                <th class="px-1 py-1.5 text-center text-[10px] font-medium text-siakad-secondary
                                    {{ $slot === 1 ? 'border-l' : '' }}" style="border-color: var(--border-color);">
                                    {{ $k->maks_input > 1 ? $slot : '' }}
                                </th>
                                @endfor
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($santriList as $i => $sk)
                        @php
                        $santri = $sk->santri;
                        $nilaiSantri = $nilaiMap[$santri->id] ?? collect();
                        $nilaiAkhir = $nilaiAkhirMap[$santri->id] ?? null;
                        @endphp
                        <tr class="dark:hover:bg-gray-700/30" id="row-{{ $santri->id }}">
                            <td class="px-5 py-3 sticky left-0 bg-white z-10"
                                style="border-right: 1px solid var(--border-color);">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                        text-xs font-bold text-white flex-shrink-0"
                                        style="background-color: var(--siakad-primary);">
                                        {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-siakad-dark truncate
                                          max-w-[140px]">
                                            {{ $santri->nama_lengkap }}
                                        </p>
                                        <p class="text-[10px] text-siakad-secondary">
                                            {{ $santri->nis }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            @foreach($komponen as $k)
                                @php $slotNilai = $nilaiSantri[$k->id] ?? collect(); @endphp
                                @for($slot = 1; $slot <= $k->maks_input; $slot++)
                                @php $nilaiExisting = $slotNilai[$slot]->nilai ?? ''; @endphp
                                <td class="px-1.5 py-2 text-center {{ $slot === 1 ? 'border-l' : '' }}"
                                    style="border-color: var(--border-color);">
                                    <input type="number"
                                        name="nilai[{{ $santri->id }}][{{ $k->id }}][{{ $slot }}]"
                                        value="{{ $nilaiExisting }}"
                                        min="0" max="100" step="0.5"
                                        placeholder="—"
                                        oninput="hitungAkhir({{ $santri->id }})"
                                        class="w-14 px-1.5 py-1.5 text-center text-sm rounded-lg border
                                          border-gray-200
                                          bg-gray-50
                                          text-siakad-dark
                                          placeholder-gray-300
                                          focus:ring-2 focus:border-transparent outline-none transition
                                          nilai-input"
                                        data-santri="{{ $santri->id }}"
                                        data-komponen="{{ $k->id }}"
                                        data-bobot="{{ $k->bobot }}">
                                </td>
                                @endfor
                            @endforeach

                            {{-- Nilai Akhir (kalkulasi live) --}}
                            <td class="px-3 py-3 text-center border-l" style="border-color: var(--border-color);">
                                <span id="nilai-akhir-{{ $santri->id }}"
                                    class="text-sm font-bold {{ $nilaiAkhir ? ($nilaiAkhir->tuntas ? 'text-green-600' : 'text-red-600') : 'text-siakad-secondary' }}">
                                    {{ $nilaiAkhir ? $nilaiAkhir->nilai_akhir : '—' }}
                                </span>
                            </td>

                            {{-- Status tuntas --}}
                            <td class="px-3 py-3 text-center">
                                <span id="status-{{ $santri->id }}">
                                    @if($nilaiAkhir)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $nilaiAkhir->tuntas
                                       ? 'bg-green-100 text-green-700'
                                       : 'bg-red-100 text-red-700' }}">
                                        {{ $nilaiAkhir->tuntas ? 'Tuntas' : 'Belum' }}
                                    </span>
                                    @else
                                    <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 flex items-center gap-3"
                style="border-top: 1px solid var(--border-color);">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan Semua Nilai
                </button>
                <a href="{{ route('guru.nilai.index') }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        const KKM = {{ $mataPelajaran->kkm }};

        // Data bobot per komponen dari server (id komponen -> bobot %)
        const bobotMap = {
            @foreach($komponen as $k)
            {{ $k->id }}: {{ $k->bobot }},
            @endforeach
        };

        function hitungAkhir(santriId) {
            const inputs = document.querySelectorAll(`.nilai-input[data-santri="${santriId}"]`);

            // Kelompokkan nilai per komponen dulu (Cara A: rata-rata per komponen
            // dari slot yang terisi saja, baru dikali bobot komponen tersebut)
            const perKomponen = {};
            inputs.forEach(input => {
                const komponenId = input.dataset.komponen;
                const val = parseFloat(input.value);
                if (!isNaN(val)) {
                    if (!perKomponen[komponenId]) perKomponen[komponenId] = [];
                    perKomponen[komponenId].push(val);
                }
            });

            let total = 0;
            let hasValue = false;
            Object.keys(perKomponen).forEach(komponenId => {
                const nilaiList = perKomponen[komponenId];
                const rata = nilaiList.reduce((a, b) => a + b, 0) / nilaiList.length;
                const bobot = parseFloat(bobotMap[komponenId] ?? 0);
                total += rata * (bobot / 100);
                hasValue = true;
            });

            const nilaiAkhirEl = document.getElementById(`nilai-akhir-${santriId}`);
            const statusEl = document.getElementById(`status-${santriId}`);

            if (hasValue) {
                const rounded = Math.round(total * 100) / 100;
                const tuntas = rounded >= KKM;

                nilaiAkhirEl.textContent = rounded;
                nilaiAkhirEl.className = `text-sm font-bold ${tuntas ? 'text-green-600' : 'text-red-600'}`;

                statusEl.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                        ${tuntas ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        ${tuntas ? 'Tuntas' : 'Belum'}
                    </span>`;
            } else {
                nilaiAkhirEl.textContent = '—';
                nilaiAkhirEl.className = 'text-sm font-bold text-siakad-secondary';
                statusEl.innerHTML = '<span class="text-gray-300 text-xs">—</span>';
            }
        }

        // Hitung semua saat load (untuk nilai yang sudah ada)
        document.querySelectorAll('.nilai-input').forEach(input => {
            if (input.value) {
                hitungAkhir(input.dataset.santri);
            }
        });
    </script>
    @endpush
</x-app-layout>
