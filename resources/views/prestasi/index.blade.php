{{-- ============================================================ --}}
{{-- resources/views/prestasi/index.blade.php                   --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Prestasi</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Catatan Prestasi</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Dokumentasikan prestasi dan pencapaian santri
            </p>
        </div>
        <a href="{{ route('kesantrian.prestasi.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Prestasi
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach([
        ['Total Prestasi', $stats['total'], 'blue'],
        ['Tahun Ini', $stats['tahun_ini'],'emerald'],
        ['Nasional+', $stats['nasional'], 'yellow'],
        ] as [$label, $value, $color])
        <div class="card-saas p-5 text-center">
            <p class="text-2xl font-bold text-{{ $color }}-600 $color }}-400
                  {{ $color === 'emerald' ? 'text-green-600' : '' }}"
                @if($color==='emerald' ) style="color: #16a34a;" @endif>
                {{ $value }}
            </p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama prestasi / santri..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <select name="jenis" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Jenis</option>
                @foreach(['akademik','non_akademik','hafalan','lainnya'] as $j)
                <option value="{{ $j }}" @selected(request('jenis')===$j)>
                    {{ ucfirst(str_replace('_', ' ', $j)) }}
                </option>
                @endforeach
            </select>
            <select name="tingkat" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Tingkat</option>
                @foreach(['pondok','kecamatan','kabupaten','provinsi','nasional','internasional'] as $t)
                <option value="{{ $t }}" @selected(request('tingkat')===$t)>
                    {{ ucfirst($t) }}
                </option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cari</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Tanggal','Santri','Prestasi','Jenis','Tingkat','Peringkat','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($prestasi as $p)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary whitespace-nowrap">
                            {{ $p->tanggal->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                                        text-white flex-shrink-0"
                                    style="background-color: var(--siakad-primary);">
                                    {{ strtoupper(substr($p->santri->nama_lengkap, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-siakad-dark text-xs">
                                        {{ $p->santri->nama_lengkap }}
                                    </p>
                                    <p class="text-[10px] text-siakad-secondary">
                                        {{ $p->santri->nis }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-siakad-dark max-w-[200px] truncate">
                            {{ $p->nama_prestasi }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                     bg-blue-100 text-blue-700">
                                {{ ucfirst(str_replace('_', ' ', $p->jenis)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ in_array($p->tingkat, ['nasional','internasional'])
                               ? 'bg-yellow-100 text-yellow-700'
                               : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($p->tingkat) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                            {{ $p->peringkat ? ucfirst(str_replace('_', ' ', $p->peringkat)) : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('kesantrian.prestasi.edit', $p) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('kesantrian.prestasi.destroy', $p) }}"
                                    onsubmit="return confirm('Hapus data prestasi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-2 rounded-lg text-siakad-secondary
                                               hover:bg-red-50 hover:text-red-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                                 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1
                                                 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada data prestasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prestasi->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $prestasi->links() }}
        </div>
        @endif
    </div>
</x-app-layout>