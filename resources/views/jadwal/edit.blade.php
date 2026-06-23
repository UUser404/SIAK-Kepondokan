<x-app-layout>
    <x-slot name="header">Edit Jadwal</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kurikulum.jadwal.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Edit Jadwal</h2>
    </div>

    <form method="POST" action="{{ route('kurikulum.jadwal.update', $jadwal) }}" class="max-w-xl">
        @csrf @method('PUT')
        @include('kurikulum.jadwal._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-app-layout>