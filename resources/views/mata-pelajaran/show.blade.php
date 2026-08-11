{{-- resources/views/mata-pelajaran/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">{{ $mataPelajaran->nama }}</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.mata-pelajaran.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">{{ $mataPelajaran->nama }}</h2>
            <p class="text-sm font-mono mt-0.5" style="color: var(--siakad-primary);">
                {{ $mataPelajaran->kode }}
            </p>
        </div>
    </div>

    <div class="max-w-2xl space-y-5">
        <div class="card-saas dark:bg-gray-800 p-5">
            <dl class="space-y-3 text-sm">
                @foreach([
                ['Kode', $mataPelajaran->kode],
                ['Nama', $mataPelajaran->nama],
                ['Tingkat', 'Kelas ' . $mataPelajaran->tingkat],
                ['Deskripsi', $mataPelajaran->deskripsi ?? '—'],
                ['Status', $mataPelajaran->is_active ? 'Aktif' : 'Nonaktif'],
                ] as [$label, $value])
                <div class="flex gap-3">
                    <dt class="w-32 flex-shrink-0 text-siakad-secondary dark:text-gray-400">{{ $label }}</dt>
                    <dd class="font-medium text-siakad-dark dark:text-white">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
        <a href="{{ route('admin.mata-pelajaran.edit', $mataPelajaran) }}"
            class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl text-white"
            style="background-color: var(--siakad-primary);">
            Edit Mata Pelajaran
        </a>
    </div>
</x-app-layout>