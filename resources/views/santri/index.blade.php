<x-app-layout>
<x-slot name="header">Data Santri</x-slot>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-siakad-dark">Data Santri</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Kelola data seluruh santri aktif pondok
        </p>
    </div>
    <a href="{{ route('admin.santri.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
       style="background-color: var(--siakad-primary);"
       onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
       onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Santri
    </a>
</div>

{{-- Filter & Search --}}
<div class="card-saas p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, NIS, NISN..."
                   class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200
                          bg-gray-50
                          text-siakad-dark
                          placeholder-gray-400
                          focus:ring-2 focus:border-transparent outline-none transition"
                   style="--tw-ring-color: rgba(35,76,106,0.2);">
        </div>

        {{-- Filter Kelas --}}
        <select name="kelas_id" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
            <option value="">Semua Kelas</option>
            @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" @selected(request('kelas_id') == $kelas->id)>
                    {{ $kelas->nama }}
                </option>
            @endforeach
        </select>

        {{-- Filter JK --}}
        <select name="jenis_kelamin" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
            <option value="">L / P</option>
            <option value="L" @selected(request('jenis_kelamin') === 'L')>Laki-laki</option>
            <option value="P" @selected(request('jenis_kelamin') === 'P')>Perempuan</option>
        </select>

        {{-- Filter Status --}}
        <select name="status" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
            <option value="aktif"  @selected(request('status','aktif') === 'aktif')>Aktif</option>
            <option value="alumni" @selected(request('status') === 'alumni')>Alumni</option>
            <option value="keluar" @selected(request('status') === 'keluar')>Keluar</option>
        </select>

        {{-- Submit search --}}
        <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white transition"
                style="background-color: var(--siakad-primary);">
            Cari
        </button>

        @if(request()->hasAny(['search','kelas_id','jenis_kelamin','status','angkatan']))
        <a href="{{ route('admin.santri.index') }}"
           class="px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 transition">
            Reset
        </a>
        @endif

        <div class="ml-auto">
            <a href="{{ route('admin.santri.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl
                      border border-gray-200
                      text-siakad-secondary
                      hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card-saas overflow-hidden">
    <div class="px-5 py-3.5 flex items-center justify-between"
         style="border-bottom: 1px solid var(--border-color);">
        <p class="text-sm text-siakad-secondary">
            Menampilkan
            <span class="font-semibold text-siakad-dark">{{ $santri->total() }}</span>
            santri
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-saas">
            <thead style="background-color: rgba(35,76,106,0.04);">
                <tr>
                    @foreach(['#','Santri','NIS','Kelas','L/P','Angkatan','Pelanggaran','Aksi'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">
                        {{ $h }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @forelse($santri as $i => $s)
                <tr class="dark:hover:bg-gray-700/50">
                    <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                        {{ $santri->firstItem() + $i }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                        text-sm font-bold text-white flex-shrink-0"
                                 style="background-color: var(--siakad-primary);">
                                {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-siakad-dark">
                                    {{ $s->nama_lengkap }}
                                </p>
                                <p class="text-xs text-siakad-secondary">
                                    {{ $s->nama_panggilan ?? '' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-siakad-secondary">
                        {{ $s->nis }}
                    </td>
                    <td class="px-5 py-3.5 text-siakad-secondary">
                        {{ $s->santriKelas->where('status','aktif')->first()?->kelas?->nama ?? '-' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $s->jenis_kelamin === 'L'
                               ? 'bg-blue-100 text-blue-700'
                               : 'bg-pink-100 text-pink-700' }}">
                            {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-siakad-secondary">
                        {{ $s->angkatan ?? '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($s->pelanggaran_aktif_count > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                         font-semibold bg-red-100 text-red-700
                                        ">
                                {{ $s->pelanggaran_aktif_count }}
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.santri.show', $s) }}"
                               class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10
                                      hover:text-siakad-primary transition"
                               title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5
                                             12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542
                                             7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.santri.edit', $s) }}"
                               class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10
                                      hover:text-siakad-primary transition"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.santri.destroy', $s) }}"
                                  onsubmit="return confirm('Nonaktifkan santri {{ addslashes($s->nama_lengkap) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-2 rounded-lg text-siakad-secondary
                                               hover:bg-red-50
                                               hover:text-red-600 transition"
                                        title="Nonaktifkan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0
                                                 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center"
                                 style="background-color: rgba(35,76,106,0.08);">
                                <svg class="w-7 h-7 text-siakad-secondary"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                             M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002
                                             5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-siakad-secondary">
                                Tidak ada data santri ditemukan
                            </p>
                            <a href="{{ route('admin.santri.create') }}"
                               class="text-sm font-medium transition-colors"
                               style="color: var(--siakad-primary);">
                                + Tambah santri pertama
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($santri->hasPages())
    <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
        {{ $santri->links() }}
    </div>
    @endif
</div>

</x-app-layout>
