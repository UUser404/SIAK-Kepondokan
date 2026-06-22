{{-- ============================================================ --}}
{{-- resources/views/tahun-ajaran/edit.blade.php                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Tahun Ajaran</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.tahun-ajaran.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">
                Edit — {{ $tahunAjaran->nama_lengkap }}
            </h2>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}" class="max-w-xl">
        @csrf @method('PUT')
        @include('tahun-ajaran._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-app-layout>