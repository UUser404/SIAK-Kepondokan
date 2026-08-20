{{-- ============================================================ --}}
{{-- resources/views/dashboards/sysadmin.blade.php               --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Administrator Sistem</x-slot>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold hidden md:block" style="color: var(--siakad-dark);">
            Sistem Administrator ⚙️
        </h1>
        <p class="text-sm mt-1 hidden md:block" style="color: var(--siakad-secondary);">
            Monitor aktivitas sistem dan manajemen akun pengguna
        </p>
    </div>

    {{-- Peringatan Tahun Ajaran aktif -- kalau kosong, banyak fitur di
         seluruh sistem (Rapor, Leger, Nilai, Presensi, dst) berhenti
         berfungsi diam-diam. Sysadmin harus tahu ini duluan. --}}
    @unless($taAktif)
    <div class="mb-6 p-4 rounded-xl flex items-start gap-3" style="background:#fef2f2; border:1px solid #fecaca;">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-red-700">Tidak ada Tahun Ajaran aktif</p>
            <p class="text-xs text-red-600 mt-0.5">
                Rapor, Leger Nilai, Input Nilai, Presensi, dan sebagian besar fitur akademik lain
                TIDAK BISA dipakai sampai ada Tahun Ajaran yang di-set aktif. Segera atur di menu Tahun Ajaran.
            </p>
        </div>
    </div>
    @endunless

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['value'=>$totalUsers, 'label'=>'Total User', 'sub'=>'terdaftar', 'route'=>route('sysadmin.users.index')],
        ['value'=>$activeUsers, 'label'=>'User Aktif', 'sub'=>'dapat login', 'route'=>route('sysadmin.users.index')],
        ['value'=>$totalLogs, 'label'=>'Aktivitas Hari Ini','sub'=>'log tercatat', 'route'=>route('sysadmin.activity-log')],
        ['value'=>$totalAiChats, 'label'=>'AI Chat Hari Ini', 'sub'=>'percakapan', 'route'=>route('sysadmin.ai-log')],
        ] as $s)
        <a href="{{ $s['route'] }}" class="card-saas p-5 block transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $s['value'] }}</p>
            <p class="text-xs font-medium mt-0.5" style="color: var(--siakad-secondary);">{{ $s['label'] }}</p>
            <p class="text-[11px] mt-0.5 text-gray-400">{{ $s['sub'] }}</p>
        </a>
        @endforeach
    </div>

    {{-- Breakdown user per role -- sebelumnya cuma angka total polos,
         sysadmin yang mau audit akses harus buka halaman User dulu. --}}
    <div class="card-saas p-5 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Komposisi User per Role</h3>
            <a href="{{ route('sysadmin.users.index') }}" class="text-xs font-medium" style="color: var(--siakad-primary);">
                Kelola User →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($roleLabels as $roleKey => $label)
            <div class="p-3 rounded-xl text-center" style="background-color: rgba(35,76,106,0.04);">
                <p class="text-lg font-bold" style="color: var(--siakad-dark);">{{ $userPerRole[$roleKey] ?? 0 }}</p>
                <p class="text-[11px] mt-0.5" style="color: var(--siakad-secondary);">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card-saas overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between"
            style="border-bottom: 1px solid var(--border-color);">
            <h3 class="font-semibold text-sm" style="color: var(--siakad-dark);">Aktivitas Sistem Hari Ini</h3>
            <a href="{{ route('sysadmin.activity-log') }}" class="text-xs font-medium"
                style="color: var(--siakad-primary);">Lihat semua →</a>
        </div>
        <div class="divide-y" style="border-color: var(--border-color);">
            @forelse($logsHariIni as $log)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                        text-white flex-shrink-0"
                    style="background-color: var(--siakad-primary);">
                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm" style="color: var(--siakad-dark);">
                        <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                        <span style="color: var(--siakad-secondary);"> · {{ $log->deskripsi }}</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $log->created_at->format('H:i:s') }} · {{ $log->ip_address }}
                    </p>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-sm" style="color: var(--siakad-secondary);">
                Belum ada aktivitas hari ini
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>