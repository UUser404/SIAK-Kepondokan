{{-- ============================================================ --}}
{{-- resources/views/surat/edit.blade.php                         --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Admin\SuratController::edit()                                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Surat</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.surat.show', $surat) }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Edit Surat — {{ $surat->nomor_surat }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">Status: <span class="capitalize">{{ $surat->status }}</span></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.surat.update', $surat) }}" class="max-w-4xl">
        @csrf
        @method('PUT')

        <div class="space-y-5">

            {{-- Santri terkait --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Santri Terkait</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">Santri (opsional)</label>
                    <select name="santri_id"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                        <option value="">-- Tidak terkait santri --</option>
                        @foreach($santriList as $s)
                        <option value="{{ $s->id }}" @selected(old('santri_id', $surat->santri_id) == $s->id)>
                            {{ $s->nama_lengkap }} ({{ $s->nis }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Detail surat --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Detail Surat</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Perihal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="perihal"
                            value="{{ old('perihal', $surat->perihal) }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Tanggal Surat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_surat"
                            value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d')) }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">Ditujukan Kepada</label>
                        <input type="text" name="ditujukan_kepada"
                            value="{{ old('ditujukan_kepada', $surat->ditujukan_kepada) }}"
                            placeholder="Nama / Instansi tujuan"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Konten Surat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="konten" rows="18" required
                        class="w-full px-3.5 py-3 text-sm rounded-xl border border-gray-200
                             bg-gray-50 text-siakad-dark placeholder-gray-400
                             focus:ring-2 outline-none resize-y transition font-mono">{{ old('konten', $surat->konten) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pb-6">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.surat.show', $surat) }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </form>
</x-app-layout>