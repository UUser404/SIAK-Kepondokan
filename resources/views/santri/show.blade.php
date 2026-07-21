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

    {{-- Banner password sekali-tampil (setelah konversi PPDB / reset password akun) --}}
    @if(session('new_password'))
    <div class="mb-6 p-4 rounded-xl border" style="background: #fffbeb; border-color: #fde68a;">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold" style="color: #92400e;">
                    Password Akun Portal (catat sekarang, tidak akan ditampilkan lagi)
                </p>
                <p class="text-xs mt-1" style="color: #92400e;">
                    Sampaikan ke santri/wali secara langsung/pribadi. Halaman ini tidak menyimpan password dalam bentuk asli.
                </p>
                <div class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white border" style="border-color: #fde68a;">
                    <code class="text-sm font-mono font-bold" style="color: #92400e;">{{ session('new_password') }}</code>
                </div>
            </div>
        </div>
    </div>
    @endif

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

        {{-- Akun Portal Santri --}}
        @php
            $akunUser = $santri->user_id ? \App\Models\User::find($santri->user_id) : null;
        @endphp
        <div class="card-saas p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Akun Portal Santri</h3>
                </div>
                @if($akunUser)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium
                             {{ $akunUser->is_active ? 'text-green-600' : 'text-gray-400' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $akunUser->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                    {{ $akunUser->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                @endif
            </div>

            @if($akunUser)
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <p class="text-sm text-siakad-dark font-medium">{{ $akunUser->email }}</p>
                    <p class="text-xs text-siakad-secondary mt-0.5">
                        {{ $akunUser->is_active
                            ? 'Santri/wali sudah bisa login memakai akun ini.'
                            : 'Akun belum aktif — aktifkan setelah kredensial disampaikan dengan aman.' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.santri.akun.toggle', $santri) }}">
                        @csrf
                        <button type="submit"
                            class="px-3 py-2 text-xs font-semibold rounded-xl border transition
                                  {{ $akunUser->is_active
                                     ? 'border-red-200 text-red-600 hover:bg-red-50'
                                     : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                            {{ $akunUser->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.santri.akun.reset-password', $santri) }}"
                        onsubmit="return confirm('Reset password akun {{ addslashes($santri->nama_lengkap) }}? Password baru akan digenerate acak.')">
                        @csrf
                        <button type="submit"
                            class="px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200
                                  text-siakad-dark hover:bg-gray-50 transition">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
            @else
            <p class="text-sm text-siakad-secondary">
                Santri ini belum punya akun portal (biasanya dibuat otomatis saat konversi dari PPDB).
            </p>
            @endif
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
