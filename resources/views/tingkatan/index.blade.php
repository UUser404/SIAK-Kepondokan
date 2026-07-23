{{-- ============================================================ --}}
{{-- resources/views/tingkatan/index.blade.php                    --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Tingkatan</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Tingkatan</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Kelola jenjang/tingkat kelas (contoh: Kelas 7-9 untuk SMP, Kelas 10-12 untuk SMA)
            </p>
        </div>
        <a href="{{ route('admin.tingkatan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Tingkatan
        </a>
    </div>

    <div class="card-saas dark:bg-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Urutan','Nama Tingkatan','Jumlah Kelas','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary dark:text-gray-400">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                    @forelse($tingkatan as $t)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">
                            {{ $t->urutan }}
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-siakad-dark dark:text-white">
                            {{ $t->nama }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">
                            {{ $t->kelas_count }} kelas
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('admin.tingkatan.edit', $t) }}"
                                    class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @if($t->kelas_count === 0)
                                <form method="POST" action="{{ route('admin.tingkatan.destroy', $t) }}"
                                    onsubmit="return confirm('Hapus tingkatan {{ $t->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                               hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                                 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0
                                                 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="p-2 text-gray-300 dark:text-gray-600" title="Masih ada kelas terdaftar, tidak bisa dihapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                             01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0
                                             00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary dark:text-gray-400">
                            Belum ada tingkatan. Tambahkan tingkatan pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>