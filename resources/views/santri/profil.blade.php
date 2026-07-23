{{-- ============================================================ --}}
{{-- resources/views/santri/profil.blade.php                      --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Admin\SantriController::profil() (route: admin.santri.profil)--}}
{{-- Catatan: saat ini belum ada tombol/link di UI manapun yang    --}}
{{-- mengarah ke halaman ini — dibuat supaya route yang sudah      --}}
{{-- terdaftar tidak 500 error kalau diakses langsung.             --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Profil Akademik Santri</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.show', $santri) }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $santri->nama_lengkap }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Profil Akademik &amp; Kesantrian · NIS: {{ $santri->nis }}
            </p>
        </div>
    </div>

    @php
    $kelasAktif = $santri->kelasAktif;
    $kamarAktif = $santri->kamarAktif;
    $nilaiAkhir = $santri->nilaiAkhir()->with('tahunAjaran')->latest('updated_at')->limit(10)->get();
    $pelanggaran = $santri->pelanggaran()->with('kategori')->latest('tanggal')->limit(5)->get();
    $prestasi = $santri->prestasi()->latest()->limit(5)->get();
    @endphp

    <div class="space-y-5 max-w-5xl">

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card-saas p-4">
                <p class="text-xs text-siakad-secondary mb-1">Kelas Aktif</p>
                <p class="text-lg font-bold text-siakad-dark">{{ $kelasAktif?->nama ?? '-' }}</p>
            </div>
            <div class="card-saas p-4">
                <p class="text-xs text-siakad-secondary mb-1">Kamar Aktif</p>
                <p class="text-lg font-bold text-siakad-dark">{{ $kamarAktif?->nomor_kamar ?? '-' }}</p>
            </div>
            <div class="card-saas p-4">
                <p class="text-xs text-siakad-secondary mb-1">Total Poin Pelanggaran</p>
                <p class="text-lg font-bold {{ $santri->total_poin_pelanggaran > 0 ? 'text-red-600' : 'text-siakad-dark' }}">
                    {{ $santri->total_poin_pelanggaran }}
                </p>
            </div>
            <div class="card-saas p-4">
                <p class="text-xs text-siakad-secondary mb-1">Jumlah Prestasi</p>
                <p class="text-lg font-bold text-siakad-dark">{{ $santri->prestasi()->count() }}</p>
            </div>
        </div>

        {{-- Nilai Akhir Terbaru --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Nilai Akhir Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Mata Pelajaran','Tahun Ajaran','Nilai Akhir','Predikat','Status'] as $h)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                       text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @forelse($nilaiAkhir as $n)
                        <tr>
                            <td class="px-5 py-3.5 text-siakad-dark font-medium">{{ $n->mataPelajaran->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $n->tahunAjaran->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-siakad-dark font-semibold">{{ $n->nilai_akhir }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $n->predikat ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $n->tuntas ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $n->tuntas ? 'Tuntas' : 'Belum Tuntas' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-siakad-secondary">
                                Belum ada nilai akhir yang tercatat
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            {{-- Riwayat Pelanggaran --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                            background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full bg-red-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Riwayat Pelanggaran Terbaru</h3>
                </div>
                <div class="divide-y" style="border-color: var(--border-color);">
                    @forelse($pelanggaran as $p)
                    <div class="px-5 py-3.5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-siakad-dark">
                                {{ $p->kategori->nama ?? '-' }}
                            </p>
                            <span class="text-xs text-siakad-secondary">
                                {{ $p->tanggal?->format('d/m/Y') }}
                            </span>
                        </div>
                        <p class="text-xs text-siakad-secondary mt-1 capitalize">Status: {{ $p->status }}</p>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-siakad-secondary">
                        Tidak ada catatan pelanggaran
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Prestasi --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                            background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full bg-green-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Prestasi Terbaru</h3>
                </div>
                <div class="divide-y" style="border-color: var(--border-color);">
                    @forelse($prestasi as $p)
                    <div class="px-5 py-3.5">
                        <p class="text-sm font-medium text-siakad-dark">{{ $p->nama_prestasi }}</p>
                        <p class="text-xs text-siakad-secondary mt-1 capitalize">
                            {{ str_replace('_', ' ', $p->tingkat) }}
                            @if($p->tanggal) · {{ $p->tanggal->format('d/m/Y') }} @endif
                        </p>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-sm text-siakad-secondary">
                        Belum ada prestasi tercatat
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>