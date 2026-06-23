<x-app-layout>
    <x-slot name="header">Jadwal Pelajaran</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Jadwal Pelajaran</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                {{ $ta?->nama_lengkap ?? 'Belum ada tahun ajaran aktif' }}
            </p>
        </div>
        <a href="{{ route('kurikulum.jadwal.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Jadwal
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-saas dark:bg-gray-800 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="kelas_id" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-gray-900 text-siakad-dark dark:text-white
                       focus:ring-2 outline-none transition">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" @selected(request('kelas_id')==$k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            <select name="hari" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-gray-900 text-siakad-dark dark:text-white
                       focus:ring-2 outline-none transition">
                <option value="">Semua Hari</option>
                {{-- Mengubah value filter menjadi Huruf Kapital Awal --}}
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                <option value="{{ $h }}" @selected(request('hari')===$h)>{{ $h }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card-saas dark:bg-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Hari','Jam','Kelas','Mata Pelajaran','Guru','Ruangan','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary dark:text-gray-400">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                    @forelse($jadwal as $j)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 capitalize font-medium text-siakad-dark dark:text-white">
                            {{ $j->hari }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-dark dark:text-white">{{ $j->kelas->nama }}</td>
                        <td class="px-5 py-3.5 text-siakad-dark dark:text-white">{{ $j->mataPelajaran->nama }}</td>
                        {{-- Menampilkan nama dari model TenagaPendidik lamamu --}}
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400 text-xs">{{ $j->guru->nama }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary dark:text-gray-400">{{ $j->ruangan ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('kurikulum.jadwal.edit', $j) }}"
                                    class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('kurikulum.jadwal.destroy', $j) }}"
                                    onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="p-2 rounded-lg text-siakad-secondary dark:text-gray-400
                                               hover:bg-red-50 hover:text-red-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                                 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0
                                                 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary dark:text-gray-400">
                            Belum ada jadwal pelajaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jadwal->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $jadwal->links() }}
        </div>
        @endif
    </div>
</x-app-layout>