{{-- ============================================================ --}}
{{-- resources/views/template-surat/_form.blade.php              --}}
{{-- ============================================================ --}}
<div class="grid lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        <div class="card-saas p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Detail Template</h3>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Nama Template <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama"
                        value="{{ old('nama', $template?->nama) }}" required
                        placeholder="e.g. Surat Keterangan Aktif"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                  bg-gray-50
                                  text-siakad-dark placeholder-gray-400
                                  focus:ring-2 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Kode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode"
                        value="{{ old('kode', $template?->kode) }}" required
                        placeholder="e.g. SKA (huruf kapital)"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                  bg-gray-50
                                  text-siakad-dark placeholder-gray-400
                                  focus:ring-2 outline-none transition uppercase">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Konten Template <span class="text-red-500">*</span>
                </label>
                <textarea name="konten" rows="20" required
                    placeholder="Tulis isi surat di sini. Gunakan placeholder dari panel kanan..."
                    class="w-full px-3.5 py-3 text-sm rounded-xl border border-gray-200
                                 bg-gray-50
                                 text-siakad-dark placeholder-gray-400
                                 focus:ring-2 outline-none resize-y transition font-mono">{{ old('konten', $template?->konten) }}</textarea>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                               hover:-translate-y-0.5"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    {{ $submitLabel }}
                </button>
                <a href="{{ route('admin.template-surat.index') }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary
                          hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </div>

    {{-- Panel placeholder --}}
    <div>
        <div class="card-saas p-5 sticky top-24">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 rounded-full bg-yellow-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Placeholder Tersedia</h3>
            </div>
            <p class="text-xs text-siakad-secondary mb-3">
                Klik untuk menyalin, lalu tempel di konten surat.
            </p>
            <div class="space-y-2">
                @foreach($placeholders as [$kode, $deskripsi])
                <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $kode }}')"
                    class="w-full text-left p-2.5 rounded-xl border border-gray-200
                               hover:border-siakad-primary/40
                               hover:bg-siakad-primary/5
                               transition group">
                    <p class="text-xs font-mono font-semibold" style="color: var(--siakad-primary);">
                        {{ $kode }}
                    </p>
                    <p class="text-[10px] text-siakad-secondary mt-0.5">
                        {{ $deskripsi }}
                    </p>
                </button>
                @endforeach
            </div>
        </div>
    </div>

</div>