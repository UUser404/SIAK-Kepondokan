{{-- ============================================================ --}}
{{-- resources/views/nilai/show.blade.php                        --}}
{{-- Spreadsheet-style input nilai                               --}}
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
            </div>
            @endforeach
        </div>
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
                    <button type="button" onclick="isiSemua()"
                        class="px-3 py-1.5 text-xs font-medium rounded-xl border border-gray-200
                           text-siakad-secondary
                           hover:bg-gray-50 transition">
                        Isi KKM Semua
                    </button>
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
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary sticky left-0 z-10 min-w-[200px]"
                                style="background-color: rgba(35,76,106,0.04);">
                                Santri
                            </th>
                            @foreach($komponen as $k)
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary min-w-[90px]">
                                {{ $k->kode }}
                                <span class="block text-[10px] font-normal normal-case opacity-70">
                                    {{ $k->bobot }}%
                                </span>
                            </th>
                            @endforeach
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               min-w-[80px]"
                                style="color: var(--siakad-primary);">
                                Akhir
                            </th>
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary min-w-[60px]">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($santriList as $i => $sk)
                        @php
                        $santri = $sk->santri;
                        $nilaiSantri= $nilaiMap[$santri->id] ?? collect();
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
                            @php
                            $nilaiExisting = $nilaiSantri[$k->id]?->nilai ?? '';
                            @endphp
                            <td class="px-2 py-2 text-center">
                                <input type="number"
                                    name="nilai[{{ $santri->id }}][{{ $k->id }}]"
                                    value="{{ $nilaiExisting }}"
                                    min="0" max="100" step="0.5"
                                    placeholder="—"
                                    oninput="hitungAkhir({{ $santri->id }})"
                                    class="w-16 px-2 py-1.5 text-center text-sm rounded-lg border
                                      border-gray-200
                                      bg-gray-50
                                      text-siakad-dark
                                      placeholder-gray-300
                                      focus:ring-2 focus:border-transparent outline-none transition
                                      nilai-input"
                                    data-santri="{{ $santri->id }}"
                                    data-bobot="{{ $k->bobot }}">
                            </td>
                            @endforeach

                            {{-- Nilai Akhir (kalkulasi live) --}}
                            <td class="px-3 py-3 text-center">
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
        const KKM = {
            {
                $mataPelajaran - > kkm
            }
        };

        // Data bobot komponen dari server
        const bobotMap = {
            @foreach($komponen as $k) {
                {
                    $k - > id
                }
            }: {
                {
                    $k - > bobot
                }
            },
            @endforeach
        };

        function hitungAkhir(santriId) {
            const inputs = document.querySelectorAll(`.nilai-input[data-santri="${santriId}"]`);
            let total = 0;
            let hasValue = false;

            inputs.forEach(input => {
                const val = parseFloat(input.value);
                const bobot = parseFloat(input.dataset.bobot);
                if (!isNaN(val)) {
                    total += val * (bobot / 100);
                    hasValue = true;
                }
            });

            const nilaiAkhirEl = document.getElementById(`nilai-akhir-${santriId}`);
            const statusEl = document.getElementById(`status-${santriId}`);

            if (hasValue) {
                const rounded = Math.round(total * 100) / 100;
                const tuntas = rounded >= KKM;

                nilaiAkhirEl.textContent = rounded;
                nilaiAkhirEl.className = `text-sm font-bold ${tuntas
            ? 'text-green-600'
            : 'text-red-600'}`;

                statusEl.innerHTML = `
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                ${tuntas
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'}">
                ${tuntas ? 'Tuntas' : 'Belum'}
            </span>`;
            } else {
                nilaiAkhirEl.textContent = '—';
                nilaiAkhirEl.className = 'text-sm font-bold text-siakad-secondary';
                statusEl.innerHTML = '<span class="text-gray-300 text-xs">—</span>';
            }
        }

        function isiSemua() {
            document.querySelectorAll('.nilai-input').forEach(input => {
                if (!input.value) {
                    input.value = KKM;
                    const santriId = input.dataset.santri;
                    hitungAkhir(santriId);
                }
            });
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