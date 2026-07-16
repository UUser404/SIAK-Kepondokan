{{-- ============================================================ --}}
{{-- resources/views/wali-kelas/nilai-ekstrakurikuler.blade.php   --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Nilai Ekstrakurikuler</x-slot>

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
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Nilai Ekstrakurikuler — {{ $kelas->nama }}</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Isi angka 0-100. Boleh dikosongkan kalau santri tidak ikut ekskul tersebut.
            </p>
        </div>
    </div>

    @if($ekskulList->isEmpty())
    <div class="p-4 mb-6 rounded-xl text-sm" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e;">
        Belum ada data ekstrakurikuler aktif. Hubungi Staf Admin untuk menambahkannya di Data Master.
    </div>
    @else

    <form method="POST" action="{{ route('wali-kelas.nilai-ekstrakurikuler.store', $kelas) }}">
        @csrf

        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 sticky left-0 z-10 min-w-[180px]"
                                style="background-color: rgba(35,76,106,0.04);">
                                Santri
                            </th>
                            @foreach($ekskulList as $ekskul)
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 border-l" style="border-color: var(--border-color);">
                                {{ $ekskul->nama }}
                                @if($ekskul->pembina)
                                <span class="block text-[10px] font-normal normal-case opacity-70">{{ $ekskul->pembina }}</span>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                        @foreach($santriList as $santri)
                        @php $nilaiSantri = $nilaiMap[$santri->id] ?? collect(); @endphp
                        <tr class="dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 z-10"
                                style="border-right: 1px solid var(--border-color);">
                                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $santri->nama_lengkap }}</p>
                                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $santri->nis }}</p>
                            </td>
                            @foreach($ekskulList as $ekskul)
                            @php $existing = $nilaiSantri[$ekskul->id]->nilai ?? ''; @endphp
                            <td class="px-3 py-2.5 text-center border-l" style="border-color: var(--border-color);">
                                <input type="number"
                                    name="nilai[{{ $santri->id }}][{{ $ekskul->id }}]"
                                    value="{{ $existing }}"
                                    min="0" max="100" step="1" placeholder="—"
                                    class="w-16 px-2 py-1.5 text-center text-sm rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white placeholder-gray-300
                                          focus:ring-2 outline-none transition">
                            </td>
                            @endforeach
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
                    Simpan Nilai Ekstrakurikuler
                </button>
            </div>
        </div>
    </form>
    @endif
</x-app-layout>
