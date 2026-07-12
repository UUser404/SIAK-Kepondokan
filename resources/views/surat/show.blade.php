{{-- ============================================================ --}}
{{-- resources/views/surat/show.blade.php                        --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Detail Surat</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.surat.index') }}"
                class="p-2 rounded-xl border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-siakad-dark">
                    {{ $surat->perihal }}
                </h2>
                <p class="text-sm font-mono mt-0.5" style="color: var(--siakad-primary);">
                    {{ $surat->nomor_surat }}
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            @if($surat->status === 'draft')
            <a href="{{ route('admin.surat.edit', $surat) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl
                  border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 transition">
                Edit
            </a>
            <form method="POST" action="{{ route('admin.surat.terbitkan', $surat) }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                           rounded-xl bg-green-600 text-white hover:bg-green-700 transition">
                    Terbitkan
                </button>
            </form>
            @endif
            <a href="{{ route('admin.surat.cetak', $surat) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl
                  text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2
                         m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2
                         2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak PDF
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Preview surat --}}
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center justify-between"
                    style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                        <h3 class="font-semibold text-sm text-siakad-dark">Preview Surat</h3>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $surat->status === 'draft'
                       ? 'bg-yellow-100 text-yellow-700'
                       : 'bg-green-100 text-green-700' }}">
                        {{ ucfirst($surat->status) }}
                    </span>
                </div>
                {{-- Konten rendered --}}
                <div class="p-6 bg-white min-h-[400px]
                        font-serif text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">
                    {{ $kontenRendered }}
                </div>
            </div>
        </div>

        {{-- Info sidebar --}}
        <div class="space-y-4">
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Informasi</h3>
                </div>
                <dl class="space-y-2.5 text-sm">
                    @foreach([
                    ['Nomor', $surat->nomor_surat],
                    ['Perihal', $surat->perihal],
                    ['Kepada', $surat->ditujukan_kepada ?? '—'],
                    ['Tanggal', $surat->tanggal_surat->locale('id')->isoFormat('D MMMM Y')],
                    ['Dibuat', $surat->dibuatOleh?->name ?? '—'],
                    ['Santri', $surat->santri?->nama_lengkap ?? '—'],
                    ['Template', $surat->templateSurat?->nama ?? '—'],
                    ] as [$label, $value])
                    <div class="flex gap-3">
                        <dt class="w-20 flex-shrink-0 text-siakad-secondary">{{ $label }}</dt>
                        <dd class="text-siakad-dark font-medium text-xs break-all">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>

    </div>
</x-app-layout>