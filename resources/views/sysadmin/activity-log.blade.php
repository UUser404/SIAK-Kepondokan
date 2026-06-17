{{-- ============================================================ --}}
{{-- resources/views/sysadmin/activity-log.blade.php             --}}
{{-- ============================================================ --}}
<x-app-layout>
<x-slot name="header">Activity Log</x-slot>

<div class="mb-6">
    <h2 class="text-xl font-semibold text-siakad-dark">Activity Log</h2>
    <p class="text-sm text-siakad-secondary mt-0.5">
        Rekam jejak seluruh aktivitas pengguna di sistem
    </p>
</div>

{{-- Filter --}}
<div class="card-saas p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[160px]">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari action / user..."
                   class="block w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
               class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                      bg-gray-50 text-siakad-dark
                      focus:ring-2 outline-none transition">
        <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Filter</button>
        <a href="{{ route('sysadmin.activity-log') }}"
           class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition">Reset</a>
    </form>
</div>

<div class="card-saas overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-saas">
            <thead style="background-color: rgba(35,76,106,0.04);">
                <tr>
                    @foreach(['Waktu','User','Action','Model','IP Address'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @forelse($logs as $log)
                <tr class="dark:hover:bg-gray-700/30">
                    <td class="px-5 py-3 text-xs text-siakad-secondary whitespace-nowrap">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-siakad-dark">
                            {{ $log->user?->name ?? 'System' }}
                        </p>
                        <p class="text-xs text-siakad-secondary">
                            {{ $log->user?->role ?? '—' }}
                        </p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium font-mono
                            @if(str_contains($log->action, 'created'))  bg-green-100 text-green-700
                            @elseif(str_contains($log->action, 'updated')) bg-blue-100 text-blue-700
                            @elseif(str_contains($log->action, 'deleted')) bg-red-100 text-red-700
                            @elseif(str_contains($log->action, 'login'))   bg-purple-100 text-purple-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-siakad-secondary">
                        {{ $log->model_type ? class_basename($log->model_type) : '—' }}
                        {{ $log->model_id ? "#{$log->model_id}" : '' }}
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-siakad-secondary">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                        Tidak ada log ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
    <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
        {{ $logs->links() }}
    </div>
    @endif
</div>
</x-app-layout>