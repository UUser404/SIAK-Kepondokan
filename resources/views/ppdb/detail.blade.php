{{-- ============================================================ --}}
{{-- resources/views/ppdb/detail.blade.php                       --}}
{{-- Detail satu pendaftar                                       --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Pendaftar</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.ppdb.pendaftar') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                {{ $pendaftar->nama_lengkap }}
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                No. Daftar: <span class="font-mono font-semibold" style="color: var(--siakad-primary);">
                    {{ $pendaftar->nomor_daftar }}
                </span>
            </p>
        </div>
    </div>

    <div class="max-w-3xl space-y-5">

        {{-- Header card --}}
        <div class="p-5 rounded-2xl bg-gradient-to-r from-siakad-primary to-siakad-dark">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-white/70 text-xs">Pendaftar PPDB</p>
                    <h2 class="text-xl font-bold text-white">{{ $pendaftar->nama_lengkap }}</h2>
                    <p class="text-white/80 text-sm mt-1">
                        {{ $pendaftar->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} ·
                        {{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir?->format('d/m/Y') }}
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-semibold
                         bg-white/20 text-white">
                    {{ ucfirst($pendaftar->status) }}
                </span>
            </div>
        </div>

        {{-- Data diri --}}
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Data Diri</h3>
            </div>
            <dl class="grid grid-cols-2 gap-x-8 gap-y-2.5 text-sm">
                @foreach([
                ['Asal Sekolah', $pendaftar->asal_sekolah ?? '—'],
                ['NISN', $pendaftar->nisn ?? '—'],
                ['Alamat', $pendaftar->alamat ?? '—'],
                ] as [$label, $value])
                <div class="flex gap-3">
                    <dt class="w-32 flex-shrink-0 text-siakad-secondary">{{ $label }}</dt>
                    <dd class="font-medium text-siakad-dark">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Data wali --}}
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Data Orang Tua / Wali</h3>
            </div>
            <dl class="grid grid-cols-2 gap-x-8 gap-y-2.5 text-sm">
                @foreach([
                ['Nama Ayah', $pendaftar->nama_ayah ?? '—'],
                ['Nama Ibu', $pendaftar->nama_ibu ?? '—'],
                ['Nama Wali', $pendaftar->nama_wali ?? '—'],
                ['No. HP Wali', $pendaftar->no_hp_wali],
                ['Email Wali', $pendaftar->email_wali ?? '—'],
                ] as [$label, $value])
                <div class="flex gap-3">
                    <dt class="w-32 flex-shrink-0 text-siakad-secondary">{{ $label }}</dt>
                    <dd class="font-medium text-siakad-dark">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Catatan admin + aksi --}}
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full bg-yellow-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Tindakan Admin</h3>
            </div>

            @if($pendaftar->catatan_admin)
            <div class="mb-4 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 text-sm
                    text-siakad-dark">
                <p class="text-xs text-siakad-secondary mb-1">Catatan Admin</p>
                {{ $pendaftar->catatan_admin }}
            </div>
            @endif

            <div class="flex flex-wrap gap-3">
                @if($pendaftar->status === 'menunggu')
                <form method="POST" action="{{ route('admin.ppdb.verifikasi', $pendaftar) }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl
                               bg-purple-100 text-purple-700 hover:bg-purple-200 transition">
                        Mulai Verifikasi
                    </button>
                </form>
                @endif

                @if(in_array($pendaftar->status, ['menunggu','verifikasi']))
                <form method="POST" action="{{ route('admin.ppdb.terima', $pendaftar) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="catatan_admin" placeholder="Catatan (opsional)"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl
                               bg-green-100 text-green-700 hover:bg-green-200 transition">
                        Terima
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.ppdb.tolak', $pendaftar) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="catatan_admin" placeholder="Alasan penolakan" required
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl
                               bg-red-100 text-red-700 hover:bg-red-200 transition">
                        Tolak
                    </button>
                </form>
                @endif

                @if($pendaftar->status === 'diterima' && !$pendaftar->santri_id)
                <form method="POST" action="{{ route('admin.ppdb.konversi', $pendaftar) }}">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Konversi ke data santri? NIS akan digenerate otomatis.')"
                        class="px-5 py-2 text-sm font-semibold rounded-xl text-white transition-all
                               hover:-translate-y-0.5 hover:shadow-lg"
                        style="background-color: var(--siakad-primary);">
                        Konversi ke Data Santri
                    </button>
                </form>
                @elseif($pendaftar->santri_id)
                <a href="{{ route('admin.santri.show', $pendaftar->santri_id) }}"
                    class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition"
                    style="background-color: var(--siakad-primary);">
                    Lihat Data Santri →
                </a>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>