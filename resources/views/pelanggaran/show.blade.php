{{-- ============================================================ --}}
{{-- resources/views/pelanggaran/show.blade.php                   --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Kesantrian\PelanggaranController::show()                      --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Pelanggaran</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kesantrian.pelanggaran.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Detail Pelanggaran</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">{{ $pelanggaran->santri->nama_lengkap }}</p>
        </div>
    </div>

    <div class="max-w-2xl space-y-5">
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center justify-between"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full bg-red-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">{{ $pelanggaran->kategori->nama }}</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize
                    {{ $pelanggaran->status === 'aktif' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ $pelanggaran->status }}
                </span>
            </div>
            <div class="p-5 space-y-4 text-sm">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-siakad-secondary text-xs mb-1">Nama Santri</dt>
                        <dd class="text-siakad-dark font-medium">
                            {{ $pelanggaran->santri->nama_lengkap }} ({{ $pelanggaran->santri->nis }})
                        </dd>
                    </div>
                    <div>
                        <dt class="text-siakad-secondary text-xs mb-1">Tanggal Kejadian</dt>
                        <dd class="text-siakad-dark font-medium">{{ $pelanggaran->tanggal?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-siakad-secondary text-xs mb-1">Tingkat</dt>
                        <dd class="text-siakad-dark font-medium capitalize">{{ $pelanggaran->kategori->tingkat }}</dd>
                    </div>
                    <div>
                        <dt class="text-siakad-secondary text-xs mb-1">Poin</dt>
                        <dd class="text-red-600 font-semibold">{{ $pelanggaran->kategori->poin }}</dd>
                    </div>
                </div>

                <div>
                    <dt class="text-siakad-secondary text-xs mb-1">Deskripsi Kejadian</dt>
                    <dd class="text-siakad-dark">{{ $pelanggaran->deskripsi }}</dd>
                </div>

                <div>
                    <dt class="text-siakad-secondary text-xs mb-1">Sanksi</dt>
                    <dd class="text-siakad-dark">{{ $pelanggaran->sanksi ?? '-' }}</dd>
                </div>

                <div class="pt-3" style="border-top: 1px solid var(--border-color);">
                    <p class="text-xs text-siakad-secondary">
                        Dicatat oleh <span class="font-medium text-siakad-dark">{{ $pelanggaran->pencatat->name ?? '-' }}</span>
                        pada {{ $pelanggaran->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ route('kesantrian.pelanggaran.edit', $pelanggaran) }}"
                        class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition-all"
                        style="background-color: var(--siakad-primary);">
                        Edit
                    </a>
                    <a href="{{ route('kesantrian.pelanggaran.index') }}"
                        class="px-4 py-2 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
