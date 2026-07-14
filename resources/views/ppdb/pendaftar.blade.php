{{-- ============================================================ --}}
{{-- resources/views/ppdb/pendaftar.blade.php                    --}}
{{-- Daftar pendaftar per periode                                --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Pendaftar PPDB</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.ppdb.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                {{ $periodeAktif?->nama ?? 'Pendaftar PPDB' }}
            </h2>
            @if($periodeAktif)
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $periodeAktif->tanggal_buka->format('d M Y') }} —
                {{ $periodeAktif->tanggal_tutup->format('d M Y') }} ·
                Sisa kuota: {{ $stats['sisa_kuota'] }}
            </p>
            @endif
        </div>
    </div>

    @if($periodeAktif && $stats)
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
        ['Total', $stats['total'], 'blue'],
        ['Menunggu', $stats['menunggu'], 'yellow'],
        ['Verifikasi', $stats['verifikasi'], 'purple'],
        ['Diterima', $stats['diterima'], 'green'],
        ['Ditolak', $stats['ditolak'], 'red'],
        ] as [$lbl, $val, $c])
        <div class="card-saas p-4 text-center">
            <p class="text-xl font-bold text-{{ $c }}-600 $c }}-400">{{ $val }}</p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $lbl }}</p>
        </div>
        @endforeach
    </div>
    @endif

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
                    placeholder="Cari nama / nomor daftar..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Status</option>
                @foreach(['menunggu','verifikasi','diterima','ditolak'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
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
                        @foreach(['No. Daftar','Nama','L/P','Asal Sekolah','No. HP Wali','Daftar','Status','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($pendaftar as $p)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 font-mono text-xs font-semibold"
                            style="color: var(--siakad-primary);">
                            {{ $p->nomor_daftar }}
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-siakad-dark">{{ $p->nama_lengkap }}</p>
                            <p class="text-xs text-siakad-secondary">
                                {{ $p->tempat_lahir }}, {{ $p->tanggal_lahir?->format('d/m/Y') }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $p->jenis_kelamin === 'L'
                               ? 'bg-blue-100 text-blue-700'
                               : 'bg-pink-100 text-pink-700' }}">
                                {{ $p->jenis_kelamin === 'L' ? 'L' : 'P' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                            {{ $p->asal_sekolah ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                            {{ $p->no_hp_wali }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary whitespace-nowrap">
                            {{ $p->created_at->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($p->status === 'menunggu')    bg-yellow-100 text-yellow-700
                            @elseif($p->status === 'verifikasi') bg-purple-100 text-purple-700
                            @elseif($p->status === 'diterima')   bg-green-100 text-green-700
                            @else bg-red-100 text-red-700 @endif">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('admin.ppdb.detail', $p) }}"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg border transition
                                      hover:bg-siakad-primary/5"
                                    style="border-color: var(--siakad-primary); color: var(--siakad-primary);">
                                    Detail
                                </a>
                                @if($p->status === 'menunggu')
                                <form method="POST" action="{{ route('admin.ppdb.verifikasi', $p) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg
                                               bg-purple-100 text-purple-700 hover:bg-purple-200
                                               transition">
                                        Verifikasi
                                    </button>
                                </form>
                                @endif
                                @if(in_array($p->status, ['menunggu','verifikasi']))
                                <form method="POST" action="{{ route('admin.ppdb.terima', $p) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-2.5 py-1 text-xs font-medium rounded-lg
                                               bg-green-100 text-green-700 hover:bg-green-200
                                               transition">
                                        Terima
                                    </button>
                                </form>
                                @endif
                                @if($p->status === 'diterima' && !$p->santri_id)
                                <form method="POST" action="{{ route('admin.ppdb.konversi', $p) }}">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Konversi ke data santri?')"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg
                                               text-white transition"
                                        style="background-color: var(--siakad-primary);">
                                        Konversi
                                    </button>
                                </form>
                                @elseif($p->santri_id)
                                <a href="{{ route('admin.santri.show', $p->santri_id) }}"
                                    class="px-2.5 py-1 text-xs font-medium rounded-lg
                                      bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                                    Lihat Santri
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada pendaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendaftar->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $pendaftar->links() }}
        </div>
        @endif
    </div>
</x-app-layout>