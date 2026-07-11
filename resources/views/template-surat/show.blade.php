{{-- ============================================================ --}}
{{-- resources/views/template-surat/show.blade.php                --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Admin\TemplateSuratController::show()                         --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Template</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.template-surat.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $templateSurat->nama }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">Kode: {{ $templateSurat->kode }}</p>
        </div>
    </div>

    <div class="max-w-4xl space-y-5">
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center justify-between"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Isi Template</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $templateSurat->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $templateSurat->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="p-5">
                <pre class="whitespace-pre-wrap font-mono text-sm text-siakad-dark
                            bg-gray-50 rounded-xl p-4"
                     style="border: 1px solid var(--border-color);">{{ $templateSurat->konten }}</pre>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.template-surat.edit', $templateSurat) }}"
                class="px-5 py-2.5 text-sm font-semibold rounded-xl text-white transition-all"
                style="background-color: var(--siakad-primary);">
                Edit Template
            </a>
            <a href="{{ route('admin.template-surat.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                Kembali
            </a>
        </div>
    </div>
</x-app-layout>
