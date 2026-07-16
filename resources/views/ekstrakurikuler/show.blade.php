{{-- resources/views/ekstrakurikuler/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">Detail Ekstrakurikuler</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.ekstrakurikuler.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">{{ $ekstrakurikuler->nama }}</h2>
    </div>

    <div class="card-saas dark:bg-gray-800 p-5 max-w-xl space-y-4">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-siakad-secondary dark:text-gray-400">Nama</dt>
                <dd class="text-siakad-dark dark:text-white font-medium">{{ $ekstrakurikuler->nama }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-siakad-secondary dark:text-gray-400">Pembina</dt>
                <dd class="text-siakad-dark dark:text-white font-medium">{{ $ekstrakurikuler->pembina ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-siakad-secondary dark:text-gray-400">Status</dt>
                <dd>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $ekstrakurikuler->is_active
                           ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                           : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $ekstrakurikuler->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
        <a href="{{ route('admin.ekstrakurikuler.edit', $ekstrakurikuler) }}"
            class="inline-block px-4 py-2 text-sm font-semibold rounded-xl text-white"
            style="background-color: var(--siakad-primary);">
            Edit
        </a>
    </div>
</x-app-layout>
