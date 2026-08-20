<x-app-layout>
    <x-slot name="header">Kategori Mata Pelajaran</x-slot>

    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">
            Kategori dipakai untuk pengelompokan mata pelajaran di rapor.
        </p>
        <a href="{{ route('admin.kategori-mata-pelajaran.create') }}"
            class="px-4 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                   hover:-translate-y-0.5 hover:shadow-lg inline-flex items-center gap-2"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
        </a>
    </div>

    <div class="card-saas dark:bg-gray-800 overflow-hidden">
        <table class="w-full table-saas text-sm">
            <thead>
                <tr style="background-color: rgba(35,76,106,0.04); border-bottom: 1px solid var(--border-color);">
                    <th class="px-5 py-3 text-left font-semibold text-siakad-dark dark:text-gray-200 w-16">Urutan</th>
                    <th class="px-5 py-3 text-left font-semibold text-siakad-dark dark:text-gray-200">Nama Kategori</th>
                    <th class="px-5 py-3 text-left font-semibold text-siakad-dark dark:text-gray-200 w-40">Dipakai Mapel</th>
                    <th class="px-5 py-3 text-right font-semibold text-siakad-dark dark:text-gray-200 w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kategoriMataPelajaran as $kategori)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td class="px-5 py-3 text-siakad-secondary dark:text-gray-400">
                        {{ $kategori->urutan ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-siakad-dark dark:text-white font-medium" dir="rtl" style="text-align: right; font-family: 'Traditional Arabic', 'Arial', sans-serif;">
                        {{ $kategori->nama }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium border"
                            style="background-color: rgba(35,76,106,0.08);
                                   color: var(--siakad-primary);
                                   border-color: rgba(35,76,106,0.2);">
                            {{ $kategori->mata_pelajaran_count }} mapel
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.kategori-mata-pelajaran.edit', $kategori) }}"
                                class="p-2 rounded-lg transition-colors" style="color: var(--text-secondary);"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form method="POST"
                                action="{{ route('admin.kategori-mata-pelajaran.destroy', $kategori) }}"
                                onsubmit="return confirm('Hapus kategori &quot;{{ $kategori->nama }}&quot;? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg transition-colors text-red-500"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-siakad-secondary dark:text-gray-400">
                        Belum ada kategori mata pelajaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>