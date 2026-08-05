{{-- ============================================================ --}}
{{-- resources/views/kkm/index.blade.php                         --}}
{{-- Matrix KKM: baris = mata pelajaran, kolom = tingkatan          --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">KKM per Tingkatan</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">KKM per Tingkatan</h2>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
            KKM bisa berbeda per tingkatan untuk mapel yang sama — kosongkan sel yang belum ditentukan.
        </p>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.kkm.store') }}">
        @csrf
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 sticky left-0"
                                style="background-color: var(--bg-card, white);">Mata Pelajaran</th>
                            @foreach($tingkatanList as $t)
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400 whitespace-nowrap">{{ $t->nama }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                        @forelse($mapelList as $mapel)
                        <tr class="dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 whitespace-nowrap sticky left-0" style="background-color: var(--bg-card, white);">
                                <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $mapel->nama }}</p>
                                @if($mapel->kategori)
                                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $mapel->kategori }}</p>
                                @endif
                            </td>
                            @foreach($tingkatanList as $t)
                            @php $nilai = $existing->get($mapel->id.'-'.$t->id)?->kkm; @endphp
                            <td class="px-3 py-3 text-center">
                                <input type="number" min="0" max="100"
                                    name="kkm[{{ $mapel->id }}][{{ $t->id }}]"
                                    value="{{ $nilai }}"
                                    placeholder="-"
                                    class="w-16 px-2 py-1.5 text-sm text-center rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $tingkatanList->count() + 1 }}"
                                class="px-4 py-12 text-center text-sm text-siakad-secondary dark:text-gray-400">
                                Belum ada mata pelajaran aktif.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white
                       transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                Simpan Semua KKM
            </button>
        </div>
    </form>
</x-app-layout>