{{-- ============================================================ --}}
{{-- resources/views/pelanggaran/index.blade.php                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Pelanggaran</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Catatan Pelanggaran</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Pantau dan catat pelanggaran tata tertib santri
            </p>
        </div>
        <a href="{{ route('kesantrian.pelanggaran.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Catat Pelanggaran
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach([
        ['Aktif', $stats['total_aktif'], 'yellow'],
        ['Berat', $stats['berat'], 'red'],
        ['Hari Ini', $stats['hari_ini'], 'blue'],
        ] as [$label, $value, $color])
        <div class="card-saas p-5 text-center">
            <p class="text-2xl font-bold text-{{ $color }}-600 $color }}-400">
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
                    placeholder="Cari nama / NIS santri..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <select name="tingkat" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Tingkat</option>
                @foreach(['ringan'=>'Ringan','sedang'=>'Sedang','berat'=>'Berat'] as $val => $lbl)
                <option value="{{ $val }}" @selected(request('tingkat')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="aktif" @selected(request('status','aktif')==='aktif' )>Aktif</option>
                <option value="selesai" @selected(request('status')==='selesai' )>Selesai</option>
                <option value="" @selected(request('status')==='' )>Semua</option>
            </select>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cari</button>
            @if(request()->hasAny(['search','tingkat','status']))
            <a href="{{ route('kesantrian.pelanggaran.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Tanggal','Santri','Pelanggaran','Tingkat','Poin','Sanksi','Status','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($pelanggaran as $p)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 whitespace-nowrap text-siakad-secondary text-xs">
                            {{ $p->tanggal->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-siakad-dark">
                                {{ $p->santri->nama_lengkap }}
                            </p>
                            <p class="text-xs text-siakad-secondary">
                                {{ $p->santri->nis }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5 max-w-[180px]">
                            <p class="font-medium text-siakad-dark text-xs">
                                {{ $p->kategori->nama }}
                            </p>
                            <p class="text-xs text-siakad-secondary truncate">
                                {{ $p->deskripsi }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $p->kategori->tingkat === 'ringan'
                               ? 'bg-yellow-100 text-yellow-700'
                               : ($p->kategori->tingkat === 'sedang'
                                  ? 'bg-orange-100 text-orange-700'
                                  : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($p->kategori->tingkat) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-siakad-dark text-center">
                            {{ $p->kategori->poin }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary max-w-[120px] truncate">
                            {{ $p->sanksi ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $p->status === 'aktif'
                               ? 'bg-red-100 text-red-700'
                               : 'bg-green-100 text-green-700' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                @if($p->status === 'aktif')
                                <form method="POST"
                                    action="{{ route('kesantrian.pelanggaran.selesai', $p) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg
                                               bg-green-100 text-green-700 hover:bg-green-200
                                               transition">
                                        Selesai
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('kesantrian.pelanggaran.edit', $p) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                            Tidak ada data pelanggaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pelanggaran->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $pelanggaran->links() }}
        </div>
        @endif
    </div>
</x-app-layout>