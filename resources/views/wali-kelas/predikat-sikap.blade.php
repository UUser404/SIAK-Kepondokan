{{-- ============================================================ --}}
{{-- resources/views/wali-kelas/predikat-sikap.blade.php          --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Predikat Sikap</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('wali-kelas.dashboard') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Predikat Sikap — {{ $kelas->nama }}</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Kedisiplinan, Kebersihan, Kerapihan, Akhlak (skala A-E)
            </p>
        </div>
    </div>

    <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
        <p class="text-xs text-siakad-secondary dark:text-gray-400 max-w-md">
            "Kedisiplinan" bisa dihitung otomatis dari data presensi gabungan semua mapel,
            lalu tetap bisa Anda ubah manual kalau perlu.
        </p>
        <form method="POST" action="{{ route('wali-kelas.predikat-sikap.hitung-kedisiplinan', $kelas) }}"
            onsubmit="return confirm('Hitung ulang Kedisiplinan semua santri dari data presensi? Kategori lain tidak akan berubah.')">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl
                      border border-gray-200 dark:border-gray-700 text-siakad-dark dark:text-white
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Hitung Ulang Kedisiplinan dari Presensi
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('wali-kelas.predikat-sikap.store', $kelas) }}">
        @csrf

        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Santri','Kedisiplinan','Kebersihan','Kerapihan','Akhlak','Catatan'] as $h)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 whitespace-nowrap">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                        @foreach($santriList as $santri)
                        @php $p = $predikatMap[$santri->id] ?? null; @endphp
                        <tr class="dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $santri->nama_lengkap }}</p>
                                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $santri->nis }}</p>
                            </td>
                            @foreach(['kedisiplinan','kebersihan','kerapihan','akhlak'] as $kategori)
                            <td class="px-4 py-3">
                                <select name="predikat[{{ $santri->id }}][{{ $kategori }}]" required
                                    class="px-2.5 py-2 text-sm rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                                    @foreach(['A','B','C','D','E'] as $huruf)
                                    <option value="{{ $huruf }}" {{ ($p?->$kategori ?? '') === $huruf ? 'selected' : '' }}>
                                        {{ $huruf }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @endforeach
                            <td class="px-4 py-3">
                                <input type="text" name="predikat[{{ $santri->id }}][catatan]"
                                    value="{{ $p?->catatan_wali_kelas }}"
                                    placeholder="Opsional"
                                    class="w-40 px-2.5 py-2 text-sm rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white placeholder-gray-400
                                          focus:ring-2 outline-none transition">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 flex items-center gap-3" style="border-top: 1px solid var(--border-color);">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan Predikat Sikap
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
