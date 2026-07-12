{{-- ============================================================ --}}
{{-- resources/views/santri/edit.blade.php                       --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Santri</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.show', $santri) }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                Edit — {{ $santri->nama_lengkap }}
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">NIS: {{ $santri->nis }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.santri.update', $santri) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('santri._form', ['submitLabel' => 'Perbarui Data Santri'])
    </form>
</x-app-layout>