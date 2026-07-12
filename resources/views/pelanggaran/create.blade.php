{{-- ============================================================ --}}
{{-- resources/views/pelanggaran/create.blade.php                --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Catat Pelanggaran</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kesantrian.pelanggaran.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Catat Pelanggaran</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Isi form di bawah untuk mencatat pelanggaran santri
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('kesantrian.pelanggaran.store') }}" class="max-w-2xl">
        @csrf
        @include('pelanggaran._form', ['pelanggaran' => null, 'submitLabel' => 'Catat Pelanggaran'])
    </form>
</x-app-layout>