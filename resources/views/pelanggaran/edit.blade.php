{{-- ============================================================ --}}
{{-- resources/views/pelanggaran/edit.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Pelanggaran</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kesantrian.pelanggaran.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark">Edit Pelanggaran</h2>
    </div>

    <form method="POST" action="{{ route('kesantrian.pelanggaran.update', $pelanggaran) }}" class="max-w-2xl">
        @csrf @method('PUT')
        @include('pelanggaran._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
</x-app-layout>