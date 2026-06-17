{{-- ============================================================ --}}
{{-- resources/views/public/ppdb-landing.blade.php               --}}
{{-- Halaman landing PPDB publik                                 --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <x-slot name="title">PPDB Online — {{ config('app.name') }}</x-slot>

    {{-- Override guest layout slot --}}
    <div class="text-center mb-10">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white"
            style="background-color: var(--siakad-primary);">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75
                     0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21a12.318 12.318
                     0 01-6.374-1.766z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">PPDB Online</h1>
        <p class="text-gray-500 mt-2">
            {{ config('siak.pondok.nama') }}
        </p>
    </div>

    @if($isOpen && $periode)
    {{-- PPDB Buka --}}
    <div class="mb-6 p-4 rounded-2xl bg-green-50
            border border-green-200 text-center">
        <p class="text-sm font-semibold text-green-700">
            ✅ PPDB sedang dibuka
        </p>
        <p class="text-xs text-green-600 mt-1">
            Periode: {{ $periode->tanggal_buka->format('d M Y') }} —
            {{ $periode->tanggal_tutup->format('d M Y') }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-8">
        <div class="text-center p-4 rounded-xl bg-gray-50">
            <p class="text-2xl font-bold text-siakad-dark">
                {{ $stats['total_daftar'] }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Total Pendaftar</p>
        </div>
        <div class="text-center p-4 rounded-xl bg-gray-50">
            <p class="text-2xl font-bold text-siakad-dark">
                {{ $stats['sisa_kuota'] }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">Sisa Kuota</p>
        </div>
    </div>

    <div class="space-y-3">
        <a href="{{ route('ppdb.public.create') }}"
            class="block w-full text-center py-3.5 text-sm font-semibold rounded-xl text-white
              transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            Daftar Sekarang →
        </a>

        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-xs text-gray-400 bg-white px-2">
                atau
            </div>
        </div>

        {{-- Cek status --}}
        <form action="{{ route('ppdb.public.cek', ':nomor') }}" method="GET"
            onsubmit="this.action=this.action.replace(':nomor', this.nomor_daftar.value)">
            <div class="flex gap-2">
                <input type="text" name="nomor_daftar" placeholder="Masukkan nomor daftar..."
                    class="flex-1 px-4 py-3 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-gray-900 placeholder-gray-400
                          focus:ring-2 outline-none transition"
                    style="--tw-ring-color: rgba(35,76,106,0.2);">
                <button type="submit"
                    class="px-4 py-3 text-sm font-medium rounded-xl border border-gray-200
                           text-gray-600
                           hover:bg-gray-50 transition">
                    Cek
                </button>
            </div>
        </form>
    </div>

    @else
    {{-- PPDB Tutup --}}
    <div class="text-center p-8 rounded-2xl bg-gray-50
            border border-gray-200 mb-6">
        <div class="w-14 h-14 bg-gray-200 rounded-2xl mx-auto mb-4
                flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002
                     2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <p class="font-semibold text-gray-700">PPDB sedang ditutup</p>
        <p class="text-sm text-gray-500 mt-1">
            Ikuti pengumuman resmi dari {{ config('siak.pondok.nama') }}
        </p>
    </div>

    {{-- Cek status saja --}}
    <form action="#" method="GET"
        onsubmit="window.location='{{ route('ppdb.public.cek', ':n') }}'.replace(':n', this.nomor_daftar.value); return false;">
        <p class="text-sm text-gray-600 mb-2 text-center">
            Sudah mendaftar sebelumnya? Cek status pendaftaran:
        </p>
        <div class="flex gap-2">
            <input type="text" name="nomor_daftar" placeholder="Nomor daftar..."
                class="flex-1 px-4 py-3 text-sm rounded-xl border border-gray-200
                      bg-gray-50
                      text-gray-900 placeholder-gray-400
                      focus:ring-2 outline-none">
            <button type="submit"
                class="px-4 py-3 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cek</button>
        </div>
    </form>
    @endif
</x-guest-layout>