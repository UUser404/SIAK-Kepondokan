{{-- ============================================================ --}}
{{-- resources/views/template-surat/edit.blade.php                --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Admin\TemplateSuratController::edit()                         --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Template</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.template-surat.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark">Edit Template — {{ $templateSurat->nama }}</h2>
    </div>

    <form method="POST" action="{{ route('admin.template-surat.update', $templateSurat) }}" class="max-w-4xl">
        @csrf
        @method('PUT')
        {{-- _form.blade.php memakai nama variabel $template (bukan $templateSurat), jadi di-remap di sini --}}
        @include('template-surat._form', ['template' => $templateSurat, 'placeholders' => $placeholders, 'submitLabel' => 'Perbarui'])
    </form>
</x-app-layout>
