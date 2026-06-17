{{-- ============================================================ --}}
{{-- resources/views/dashboards/admin.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @php
    $hour = now()->hour;
    if ($hour < 11) { $greeting='Selamat Pagi' ; $emoji='🌅' ; }
        elseif ($hour < 15) { $greeting='Selamat Siang' ; $emoji='☀️' ; }
        elseif ($hour < 18) { $greeting='Selamat Sore' ; $emoji='🌤️' ; }
        else { $greeting='Selamat Malam' ; $emoji='🌙' ; }
        @endphp

        <div class="mb-8">
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            {{ $greeting }}, {{ explode(' ', Auth::user()->name)[0] }}! {{ $emoji }}
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Ringkasan data dan aktivitas sistem hari ini
        </p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach([
            ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'value'=>$totalSantri, 'label'=>'Santri Aktif'],
            ['icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'value'=>$totalPendidik, 'label'=>'Tenaga Pendidik'],
            ['icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21a12.318 12.318 0 01-6.374-1.766z', 'value'=>$totalPpdb, 'label'=>'Menunggu PPDB'],
            ['icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'value'=>$totalSurat, 'label'=>'Surat Bulan Ini'],
            ] as $stat)
            <div class="card-saas p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                        style="background-color: rgba(35,76,106,0.1);">
                        <svg class="w-5 h-5" style="color: var(--siakad-primary);"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $stat['value'] }}</p>
                        <p class="text-xs" style="color: var(--siakad-secondary);">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="card-saas overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between"
                style="border-bottom: 1px solid var(--border-color);">
                <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Pendaftar PPDB Terbaru</h3>
                <a href="{{ route('admin.ppdb.index') }}" class="text-xs font-medium transition-colors"
                    style="color: var(--siakad-primary);">Lihat semua →</a>
            </div>
            <div class="divide-y" style="border-color: var(--border-color);">
                @forelse($ppdbTerbaru as $p)
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--siakad-dark);">{{ $p->nama_lengkap }}</p>
                        <p class="text-xs" style="color: var(--siakad-secondary);">
                            {{ $p->nomor_daftar }} · {{ $p->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($p->status === 'menunggu')   bg-yellow-100 text-yellow-700
                @elseif($p->status === 'verifikasi') bg-blue-100 text-blue-700
                @elseif($p->status === 'diterima')   bg-green-100 text-green-700
                @else bg-red-100 text-red-700 @endif">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-sm" style="color: var(--siakad-secondary);">
                    Belum ada pendaftar
                </div>
                @endforelse
            </div>
        </div>
</x-app-layout>