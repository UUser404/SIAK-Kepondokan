{{-- ============================================================ --}}
{{-- resources/views/wali-kelas/predikat-sikap.blade.php          --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Predikat Sikap</x-slot>

    <div class="mb-6 flex items-center gap-3">
        {{-- Tombol kembali pakai history.back() (bukan hardcode ke
             wali-kelas.dashboard) -- kalau wali kelas cuma pegang 1 kelas,
             link "Dashboard Wali Kelas" di sidebar DISEMBUNYIKAN (lihat
             sidebar-nav.blade.php), jadi user sebenarnya datang LANGSUNG
             dari sidebar, bukan dari dashboard. Kalau tombol ini di-hardcode
             ke wali-kelas.dashboard, user malah dilempar ke halaman yang
             "harusnya" tidak dia akses. history.back() otomatis balik ke
             manapun user datang, benar untuk kedua kasus (1 kelas atau
             >1 kelas) tanpa perlu hitung ulang jumlah kelas di sini.
             href tetap diisi (bukan "#") sebagai fallback kalau JS mati
             atau dibuka di tab baru lewat klik-kanan. --}}
        <a href="{{ route('guru.dashboard') }}" onclick="history.back(); return false;"
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
                Akhlaq, Kerajinan, Kebersihan, Kedisiplinan (skala A-B-C) — sepenuhnya dinilai manual
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('wali-kelas.predikat-sikap.store', $kelas) }}">
        @csrf

        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Santri','Akhlaq','Kerajinan','Kebersihan','Kedisiplinan','Sakit','Izin','Alpa','Catatan'] as $h)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 whitespace-nowrap">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                        @foreach($santriList as $santri)
                        @php
                        $p = $predikatMap[$santri->id] ?? null;
                        $auto = $kehadiranAuto[$santri->id] ?? ['sakit' => 0, 'izin' => 0, 'alpa' => 0];
                        @endphp
                        <tr class="dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $santri->nama_lengkap }}</p>
                                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $santri->nis }}</p>
                            </td>
                            @foreach(['akhlak','kerajinan','kebersihan','kedisiplinan'] as $kategori)
                            <td class="px-4 py-3">
                                <select name="predikat[{{ $santri->id }}][{{ $kategori }}]" required
                                    class="w-20 px-2.5 py-2 text-sm rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                                    @foreach(['A','B','C'] as $huruf)
                                    <option value="{{ $huruf }}" {{ ($p?->$kategori ?? '') === $huruf ? 'selected' : '' }}>
                                        {{ $huruf }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @endforeach
                            @foreach(['sakit', 'izin', 'alpa'] as $jenis)
                            <td class="px-4 py-3">
                                <input type="number" min="0"
                                    name="predikat[{{ $santri->id }}][{{ $jenis }}]"
                                    value="{{ $p?->{$jenis.'_override'} ?? $auto[$jenis] }}"
                                    class="w-16 px-2 py-2 text-sm rounded-lg border text-center
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
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