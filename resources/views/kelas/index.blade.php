<x-app-layout>
    <x-slot name="header">Kelas</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Manajemen Kelas</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                {{ $ta?->nama_lengkap ?? 'Belum ada tahun ajaran aktif' }}
            </p>
        </div>
        <a href="{{ route('kurikulum.kelas.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kelas
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-saas dark:bg-gray-800 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <select name="tingkatan_id" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-gray-900 text-siakad-dark dark:text-white
                       focus:ring-2 outline-none transition">
                <option value="">Semua Tingkatan</option>
                @foreach($tingkatanList as $t)
                <option value="{{ $t->id }}" @selected(request('tingkatan_id')==$t->id)>{{ $t->nama }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($kelas as $k)
        <div class="card-saas dark:bg-gray-800 p-5 hover:border-siakad-primary/30 transition">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-siakad-dark dark:text-white text-lg">{{ $k->nama }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $k->tingkatan->nama }}</p>
                </div>
                <div class="flex gap-1">
                    <a href="{{ route('kurikulum.kelas.edit', $k) }}"
                        class="p-1.5 rounded-lg text-siakad-secondary dark:text-gray-400
                          hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                 m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 mb-3 text-xs text-siakad-secondary dark:text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ $k->waliKelas?->name ?? 'Belum ada wali kelas' }}
            </div>

            <div class="flex items-center justify-between text-sm mb-4">
                <span class="text-siakad-secondary dark:text-gray-400">Santri</span>
                <span class="font-semibold text-siakad-dark dark:text-white">
                    {{ $k->jumlah_santri }}/{{ $k->kapasitas }}
                </span>
            </div>

            <a href="{{ route('kurikulum.kelas.show', $k) }}"
                class="block w-full text-center py-2 text-xs font-semibold rounded-xl border
                  transition hover:-translate-y-0.5"
                style="border-color: var(--siakad-primary); color: var(--siakad-primary);">
                Lihat Detail
            </a>
        </div>
        @empty
        <div class="col-span-3 card-saas dark:bg-gray-800 p-16 text-center">
            <p class="text-siakad-secondary dark:text-gray-400 text-sm">Belum ada kelas</p>
        </div>
        @endforelse
    </div>

    @if($kelas->hasPages())
    <div class="mt-6">{{ $kelas->links() }}</div>
    @endif
</x-app-layout>