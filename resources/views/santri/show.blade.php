{{-- ============================================================ --}}
{{-- resources/views/santri/show.blade.php - revised             --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Profil Santri</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $santri->nama_lengkap }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">NIS: {{ $santri->nis }}</p>
        </div>
    </div>

    <div class="space-y-5 max-w-4xl">

        {{-- Header Card --}}
        <div class="card-saas overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-siakad-primary to-siakad-dark">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white/20 flex-shrink-0
                            flex items-center justify-center">
                        @if($santri->foto)
                        <img src="{{ Storage::url($santri->foto) }}" class="w-full h-full object-cover">
                        @else
                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                        </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold text-white">{{ $santri->nama_lengkap }}</h2>
                        <p class="text-white/80 text-sm mt-1">
                            NIS: {{ $santri->nis }}
                            @if($santri->nisn) · NISN: {{ $santri->nisn }} @endif
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                     bg-white/20 text-white capitalize">
                                {{ $santri->status }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white">
                                {{ $santri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                            @if($kelas = $santri->santriKelas->where('status','aktif')->first()?->kelas)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white">
                                {{ $kelas->nama }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.santri.edit', $santri) }}"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold
                          bg-white/20 hover:bg-white/30 text-white transition">
                        Edit Data
                    </a>
                </div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid md:grid-cols-2 gap-5">
            {{-- Identitas --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Identitas</h3>
                </div>
                <dl class="space-y-2.5 text-sm">
                    @foreach([
                    ['Tempat, Tgl Lahir', ($santri->tempat_lahir ?? '-') . ', ' . ($santri->tanggal_lahir?->format('d/m/Y') ?? '-')],
                    ['Asal Sekolah', $santri->asal_sekolah ?? '-'],
                    ['Angkatan', $santri->angkatan ?? '-'],
                    ['No. HP', $santri->no_hp_santri ?? '-'],
                    ['Alamat', $santri->alamat ?? '-'],
                    ] as [$label, $value])
                    <div class="flex gap-3">
                        <dt class="w-36 flex-shrink-0 text-siakad-secondary">{{ $label }}</dt>
                        <dd class="text-siakad-dark font-medium flex-1">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            {{-- Data Wali --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Data Wali</h3>
                </div>
                <dl class="space-y-2.5 text-sm">
                    @foreach([
                    ['Nama Ayah', $santri->nama_ayah ?? '-'],
                    ['Nama Ibu', $santri->nama_ibu ?? '-'],
                    ['Nama Wali', $santri->nama_wali ?? '-'],
                    ['No. HP Wali', $santri->no_hp_wali ?? '-'],
                    ['Pekerjaan Wali', $santri->pekerjaan_wali ?? '-'],
                    ] as [$label, $value])
                    <div class="flex gap-3">
                        <dt class="w-32 flex-shrink-0 text-siakad-secondary">{{ $label }}</dt>
                        <dd class="text-siakad-dark font-medium">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Asrama --}}
        @if($kamar = $santri->penempatanKamar->where('is_aktif', true)->first())
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Asrama</h3>
            </div>
            <p class="text-sm text-siakad-dark">
                <span class="font-medium">{{ $kamar->kamar->asrama->nama }}</span>
                <span class="text-siakad-secondary mx-2">·</span>
                Kamar {{ $kamar->kamar->nomor_kamar }}
                <span class="text-siakad-secondary ml-2 text-xs">
                    Lantai {{ $kamar->kamar->lantai ?? '-' }}
                </span>
            </p>
        </div>
        @endif

        {{-- Pelanggaran aktif --}}
        @if($santri->pelanggaran->where('status','aktif')->count() > 0)
        <div class="rounded-xl p-5 border border-red-200"
            style="background: #fef2f2;">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-4 rounded-full bg-red-500"></div>
                <h3 class="font-semibold text-sm text-red-700">
                    Pelanggaran Aktif ({{ $santri->pelanggaran->where('status','aktif')->count() }})
                </h3>
            </div>
            <div class="space-y-2">
                @foreach($santri->pelanggaran->where('status','aktif')->take(3) as $p)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-red-800">{{ $p->kategori->nama }}</span>
                    <span class="text-red-500 text-xs">
                        {{ $p->tanggal->format('d/m/Y') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>