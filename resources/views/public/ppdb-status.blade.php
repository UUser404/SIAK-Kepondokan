{{-- ============================================================ --}}
{{-- resources/views/public/ppdb-status.blade.php                --}}
{{-- Cek status pendaftaran                                      --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <x-slot name="title">Status Pendaftaran</x-slot>

    <div class="mb-8">
        <a href="{{ route('ppdb.public.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
            ← Kembali
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Status Pendaftaran</h2>
        <p class="text-sm text-gray-500 mt-1">
            No. Daftar: <span class="font-mono font-semibold">{{ $nomor_daftar }}</span>
        </p>
    </div>

    @if($pendaftar)

    @php
    $statusConfig = [
    'menunggu' => ['bg-yellow-50','border-yellow-200','text-yellow-700','⏳','Menunggu Verifikasi','Pendaftaran Anda telah diterima dan sedang menunggu verifikasi dari panitia.'],
    'verifikasi' => ['bg-blue-50','border-blue-200','text-blue-700','🔍','Sedang Diverifikasi','Berkas pendaftaran Anda sedang diverifikasi oleh panitia PPDB.'],
    'diterima' => ['bg-green-50','border-green-200','text-green-700','✅','Diterima','Selamat! Anda diterima di '.config('siak.pondok.nama').'. Silakan hubungi panitia untuk langkah selanjutnya.'],
    'ditolak' => ['bg-red-50','border-red-200','text-red-700','❌','Tidak Diterima','Mohon maaf, pendaftaran Anda tidak dapat diterima pada periode ini.'],
    'mengundurkan_diri' => ['bg-gray-50','border-gray-200','text-gray-600','🔄','Mengundurkan Diri','Pendaftaran Anda telah dibatalkan.'],
    ];
    [$bgClass,$borderClass,$textClass,$emoji,$label,$desc] = $statusConfig[$pendaftar->status] ?? $statusConfig['menunggu'];
    @endphp

    <div class="p-5 rounded-2xl border {{ $bgClass }} {{ $borderClass }} mb-6 text-center">
        <p class="text-4xl mb-2">{{ $emoji }}</p>
        <p class="font-bold {{ $textClass }} text-lg">{{ $label }}</p>
        <p class="text-sm {{ $textClass }} opacity-80 mt-1">{{ $desc }}</p>
        @if($pendaftar->catatan_admin)
        <p class="text-sm {{ $textClass }} mt-2 font-medium">
            Catatan: {{ $pendaftar->catatan_admin }}
        </p>
        @endif
    </div>

    <div class="card-saas p-5 space-y-2.5 text-sm mb-6">
        @foreach([
        ['Nama Lengkap', $pendaftar->nama_lengkap],
        ['Nomor Daftar', $pendaftar->nomor_daftar],
        ['Jenis Kelamin', $pendaftar->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'],
        ['Asal Sekolah', $pendaftar->asal_sekolah ?? '—'],
        ['Tanggal Daftar', $pendaftar->created_at->locale('id')->isoFormat('D MMMM Y')],
        ] as [$label, $value])
        <div class="flex gap-3">
            <dt class="w-36 flex-shrink-0 text-gray-500">{{ $label }}</dt>
            <dd class="font-medium text-gray-800">{{ $value }}</dd>
        </div>
        @endforeach
    </div>

    @else

    <div class="text-center p-10 rounded-2xl bg-gray-50
            border border-gray-200">
        <p class="text-4xl mb-3">🔎</p>
        <p class="font-semibold text-gray-700">Data tidak ditemukan</p>
        <p class="text-sm text-gray-500 mt-1">
            Nomor daftar "<span class="font-mono">{{ $nomor_daftar }}</span>" tidak terdaftar.
        </p>
    </div>

    @endif

    <div class="text-center mt-6">
        <a href="{{ route('ppdb.public.index') }}"
            class="text-sm font-medium transition-colors"
            style="color: var(--siakad-primary);">
            ← Kembali ke Halaman PPDB
        </a>
    </div>
</x-guest-layout>