{{-- resources/views/komponen-nilai/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Komponen Nilai</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Komponen Nilai</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Atur bobot komponen penilaian per mata pelajaran
            </p>
        </div>
        <a href="{{ route('admin.komponen-nilai.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Komponen
        </a>
    </div>

    {{-- Search --}}
    <div class="card-saas dark:bg-gray-800 p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama / kode komponen..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cari</button>
        </form>
    </div>

    <div class="card-saas dark:bg-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Urutan','Kode','Nama Komponen','Mata Pelajaran','Tipe','Bobot','Status','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary dark:text-gray-400">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                    @forelse($komponen as $k)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">{{ $k->urutan }}</td>
                        <td class="px-5 py-3.5 font-mono text-xs font-semibold"
                            style="color: var(--siakad-primary);">{{ $k->kode }}</td>
                        <td class="px-5 py-3.5 font-medium text-siakad-dark dark:text-white">{{ $k->nama }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">
                            {{ $k->mataPelajaran->nama ?? '—' }}
                            <span class="text-xs">(Kelas {{ $k->mataPelajaran->tingkat ?? '?' }})</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                            $tipeColor = match($k->tipe) {
                            'harian' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'uts' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            'uas' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            'praktik' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                            default => 'bg-gray-100 text-gray-500',
                            };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipeColor }}">
                                {{ ucfirst($k->tipe) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-siakad-dark dark:text-white">{{ $k->bobot }}%</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $k->is_active
                               ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                               : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $k->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.komponen-nilai.show', $k) }}"
                                    class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523
                                             5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064
                                             7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.komponen-nilai.edit', $k) }}"
                                    class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828