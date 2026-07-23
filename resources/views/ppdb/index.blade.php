{{-- ============================================================ --}}
{{-- resources/views/ppdb/index.blade.php                        --}}
{{-- Daftar periode PPDB                                         --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">PPDB</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">PPDB Online</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Kelola periode dan pendaftar penerimaan santri baru
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ppdb.public.index') }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium
                  border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Halaman Publik
            </a>
            <a href="{{ route('admin.ppdb.create-periode') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                  text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Periode
            </a>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($periodeList as $periode)
        <div class="card-saas overflow-hidden">
            <div class="p-5">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-siakad-dark">
                                {{ $periode->nama }}
                            </h3>
                            @if($periode->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                     bg-green-100 text-green-700">
                                Aktif
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-siakad-secondary">
                            {{ $periode->tahunAjaran->nama_lengkap }} ·
                            {{ $periode->tanggal_buka->format('d M Y') }} —
                            {{ $periode->tanggal_tutup->format('d M Y') }} ·
                            Kuota: {{ $periode->kuota }}
                        </p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @if(!$periode->is_active)
                        <form method="POST" action="{{ route('admin.ppdb.aktifkan', $periode) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium rounded-xl border
                                       border-gray-200
                                       text-siakad-secondary
                                       hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Aktifkan
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('admin.ppdb.pendaftar', $periode) }}"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl text-white transition"
                            style="background-color: var(--siakad-primary);">
                            Lihat Pendaftar
                        </a>
                    </div>
                </div>

                {{-- Progress bar kuota --}}
                @php
                $persen = $periode->kuota > 0
                ? round(($periode->diterima / $periode->kuota) * 100) : 0;
                @endphp
                <div class="mt-4 grid grid-cols-4 gap-3 mb-3">
                    @foreach([
                    ['Total', $periode->total_pendaftar, 'blue'],
                    ['Menunggu', $periode->menunggu, 'yellow'],
                    ['Diterima', $periode->diterima, 'green'],
                    ['Ditolak', $periode->ditolak, 'red'],
                    ] as [$lbl, $val, $c])
                    <div class="text-center p-2 rounded-xl bg-{{ $c }}-50 $c }}-900/20">
                        <p class="text-lg font-bold text-{{ $c }}-600 $c }}-400">{{ $val }}</p>
                        <p class="text-[10px] text-{{ $c }}-500">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-siakad-secondary">Kuota terisi</span>
                        <span class="font-semibold text-siakad-dark">
                            {{ $periode->diterima }}/{{ $periode->kuota }} ({{ $persen }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all"
                            style="width: {{ min($persen, 100) }}%;
                                background-color: {{ $persen >= 100 ? '#dc2626' : 'var(--siakad-primary)' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card-saas p-16 text-center">
            <p class="text-siakad-secondary text-sm">
                Belum ada periode PPDB. Buat periode baru untuk memulai.
            </p>
        </div>
        @endforelse
    </div>
</x-app-layout>