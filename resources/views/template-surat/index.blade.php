{{-- ============================================================ --}}
{{-- resources/views/template-surat/index.blade.php              --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Template Surat</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Template Surat</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Kelola template untuk mempercepat pembuatan surat
            </p>
        </div>
        <a href="{{ route('admin.template-surat.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Template
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $t)
        <div class="card-saas overflow-hidden hover:border-siakad-primary/30 transition">
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-siakad-dark">{{ $t->nama }}</p>
                        <p class="text-xs font-mono mt-0.5" style="color: var(--siakad-secondary);">
                            {{ $t->kode }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                    {{ $t->is_active
                       ? 'bg-green-100 text-green-700'
                       : 'bg-gray-100 text-gray-500' }}">
                        {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="text-xs text-siakad-secondary line-clamp-3 font-mono
                       bg-gray-50 rounded-lg p-2 mb-4">
                    {{ Str::limit($t->konten, 100) }}
                </p>
            </div>
            <div class="px-5 py-3 flex items-center gap-2"
                style="border-top: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.02);">
                <a href="{{ route('admin.template-surat.edit', $t) }}"
                    class="flex-1 text-center py-1.5 text-xs font-medium rounded-lg border
                      border-gray-200
                      text-siakad-secondary
                      hover:bg-gray-50 transition">
                    Edit
                </a>
                <a href="{{ route('admin.surat.create', ['template_id' => $t->id]) }}"
                    class="flex-1 text-center py-1.5 text-xs font-semibold rounded-lg text-white transition"
                    style="background-color: var(--siakad-primary);">
                    Pakai
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 card-saas p-16 text-center">
            <p class="text-siakad-secondary text-sm">
                Belum ada template. Buat template untuk mempercepat penulisan surat.
            </p>
        </div>
        @endforelse
    </div>
</x-app-layout>