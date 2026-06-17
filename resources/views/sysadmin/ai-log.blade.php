{{-- ============================================================ --}}
{{-- resources/views/sysadmin/ai-log.blade.php                   --}}
{{-- ============================================================ --}}
<x-app-layout>
<x-slot name="header">AI Log</x-slot>

<div class="mb-6">
    <h2 class="text-xl font-semibold text-siakad-dark">AI Conversation Log</h2>
    <p class="text-sm text-siakad-secondary mt-0.5">
        Riwayat seluruh percakapan dengan AI Advisor
    </p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['Total Percakapan', $stats['total']],
        ['Hari Ini',         $stats['hari_ini']],
        ['Avg Response',     $stats['avg_response_ms'] . ' ms'],
    ] as [$label, $value])
    <div class="card-saas p-5 text-center">
        <p class="text-2xl font-bold text-siakad-dark">{{ $value }}</p>
        <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
    </div>
    @endforeach
</div>

<div class="card-saas overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-saas">
            <thead style="background-color: rgba(35,76,106,0.04);">
                <tr>
                    @foreach(['Waktu','User','Pertanyaan','Jawaban','Model','Response Time'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @forelse($logs as $log)
                <tr class="dark:hover:bg-gray-700/30">
                    <td class="px-5 py-3 text-xs text-siakad-secondary whitespace-nowrap">
                        {{ $log->created_at->format('d/m H:i') }}
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-xs font-medium text-siakad-dark">
                            {{ $log->user?->name ?? '—' }}
                        </p>
                        <p class="text-[10px] text-siakad-secondary">
                            {{ $log->user?->role ?? '' }}
                        </p>
                    </td>
                    <td class="px-5 py-3 max-w-[200px]">
                        <p class="text-xs text-siakad-dark truncate" title="{{ $log->question }}">
                            {{ $log->question }}
                        </p>
                    </td>
                    <td class="px-5 py-3 max-w-[200px]">
                        <p class="text-xs text-siakad-secondary truncate"
                           title="{{ $log->answer }}">
                            {{ $log->answer }}
                        </p>
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-siakad-secondary">
                        {{ $log->model_used ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs
                               {{ ($log->response_time_ms ?? 0) > 5000
                                  ? 'text-red-600 font-semibold'
                                  : 'text-siakad-secondary' }}">
                        {{ $log->response_time_ms ? $log->response_time_ms . ' ms' : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                        Belum ada log AI
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($logs) && method_exists($logs,'hasPages') && $logs->hasPages())
    <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
        {{ $logs->links() }}
    </div>
    @endif
</div>
</x-app-layout>
