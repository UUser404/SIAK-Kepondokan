{{-- ============================================================ --}}
{{-- resources/views/wali-kelas/dashboard.blade.php               --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Dashboard Wali Kelas</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Dashboard Wali Kelas</h2>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
            Kelas yang Anda ampu sebagai wali kelas
            @if($ta) &middot; Tahun Ajaran {{ $ta->nama }} ({{ ucfirst($ta->semester) }}) @endif
        </p>
    </div>

    @if(!$ta)
    <div class="p-4 mb-6 rounded-xl text-sm" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e;">
        Belum ada tahun ajaran aktif.
    </div>
    @endif

    <div class="grid md:grid-cols-2 gap-5">
        @forelse($kelasList as $kelas)
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="px-5 py-3.5 flex items-center justify-between"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">{{ $kelas->nama }}</h3>
                </div>
                <span class="text-xs text-siakad-secondary dark:text-gray-400">
                    {{ $kelas->jumlah_santri }} santri
                </span>
            </div>
            <div class="p-4 grid grid-cols-3 gap-2">
                <a href="{{ route('wali-kelas.predikat-sikap.index', $kelas) }}"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center transition
                          border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" style="color: var(--siakad-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[11px] font-medium text-siakad-dark dark:text-gray-200">Predikat Sikap</span>
                </a>
                <a href="{{ route('wali-kelas.nilai-ekstrakurikuler.index', $kelas) }}"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center transition
                          border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" style="color: var(--siakad-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span class="text-[11px] font-medium text-siakad-dark dark:text-gray-200">Nilai Ekskul</span>
                </a>
                <a href="{{ route('wali-kelas.rapor.index') }}"
                    class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center transition
                          border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" style="color: var(--siakad-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-[11px] font-medium text-siakad-dark dark:text-gray-200">Rapor</span>
                </a>
            </div>
        </div>
        @empty
        <div class="card-saas dark:bg-gray-800 p-10 text-center md:col-span-2">
            <p class="text-sm text-siakad-secondary dark:text-gray-400">
                Anda belum ditugaskan sebagai wali kelas manapun di tahun ajaran aktif.
            </p>
        </div>
        @endforelse
    </div>
</x-app-layout>
