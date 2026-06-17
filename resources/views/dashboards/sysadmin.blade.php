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

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
        ['value'=>$totalUsers, 'label'=>'Total User', 'sub'=>'terdaftar'],
        ['value'=>$activeUsers, 'label'=>'User Aktif', 'sub'=>'dapat login'],
        ['value'=>$totalLogs, 'label'=>'Aktivitas Hari Ini','sub'=>'log tercatat'],
        ['value'=>$totalAiChats, 'label'=>'AI Chat Hari Ini', 'sub'=>'percakapan'],
        ] as $s)
        <div class="card-saas p-5">
            <p class="text-2xl font-bold" style="color: var(--siakad-dark);">{{ $s['value'] }}</p>
            <p class="text-xs font-medium mt-0.5" style="color: var(--siakad-secondary);">{{ $s['label'] }}</p>
            <p class="text-[11px] mt-0.5 text-gray-400">{{ $s['sub'] }}</p>
        </div>
        @endforeach
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
                        <span style="color: var(--siakad-secondary);"> · {{ $log->action }}</span>
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