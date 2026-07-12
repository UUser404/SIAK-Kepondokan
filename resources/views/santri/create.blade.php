{{-- ============================================================ --}}
{{-- resources/views/santri/create.blade.php                     --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Tambah Santri</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Tambah Santri Baru</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Isi data lengkap santri baru
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.santri.store') }}" enctype="multipart/form-data">
        @csrf
        @include('santri._form', ['santri' => null, 'submitLabel' => 'Simpan Data Santri'])
    </form>
</x-app-layout>