{-- ============================================================ --}}
{{-- resources/views/surat/create.blade.php                      --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Buat Surat</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.surat.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Buat Surat Keluar</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Nomor surat akan digenerate otomatis
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.surat.store') }}" class="max-w-4xl">
        @csrf

        <div class="space-y-5">

            {{-- Template & Santri selector --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Pilih Template & Santri</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Template Surat
                        </label>
                        <select name="template_surat_id" id="template-select"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                            <option value="">-- Tanpa Template --</option>
                            @foreach($templates as $t)
                            <option value="{{ $t->id }}" @selected($template?->id == $t->id)>
                                {{ $t->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Santri (opsional)
                        </label>
                        <select name="santri_id" id="santri-select"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                            <option value="">-- Tidak terkait santri --</option>
                            @foreach($santriList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" id="btn-muat-template"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition
                           border-gray-200
                           text-siakad-secondary
                           hover:bg-gray-50">
                        Muat Template
                    </button>
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
                        <input type="text" name="perihal" id="input-perihal"
                            value="{{ old('perihal') }}" required
                            placeholder="e.g. Keterangan Aktif Santri"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Tanggal Surat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_surat"
                            value="{{ old('tanggal_surat', today()->format('Y-m-d')) }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-siakad-dark focus:ring-2 outline-none transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Ditujukan Kepada
                        </label>
                        <input type="text" name="ditujukan_kepada"
                            value="{{ old('ditujukan_kepada') }}"
                            placeholder="Nama / Instansi tujuan"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                    </div>
                </div>

                {{-- Konten surat --}}
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Konten Surat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="konten" id="konten-surat" rows="18" required
                        placeholder="Isi surat..."
                        class="w-full px-3.5 py-3 text-sm rounded-xl border border-gray-200
                             bg-gray-50
                             text-siakad-dark placeholder-gray-400
                             focus:ring-2 outline-none resize-y transition font-mono">{{ old('konten', $template?->konten) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pb-6">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan sebagai Draft
                </button>
                <a href="{{ route('admin.surat.index') }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        document.getElementById('btn-muat-template').addEventListener('click', async function() {
            const templateId = document.getElementById('template-select').value;
            const santriId = document.getElementById('santri-select').value;

            if (!templateId) {
                alert('Pilih template terlebih dahulu.');
                return;
            }

            this.textContent = 'Memuat...';
            this.disabled = true;

            try {
                const params = new URLSearchParams({
                    template_id: templateId
                });
                if (santriId) params.append('santri_id', santriId);

                const res = await fetch(`{{ route('admin.surat.template-konten') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();

                document.getElementById('konten-surat').value = data.konten;
                document.getElementById('input-perihal').value = data.perihal;
            } catch (e) {
                alert('Gagal memuat template.');
            } finally {
                this.textContent = 'Muat Template';
                this.disabled = false;
            }
        });
    </script>
    @endpush
</x-app-layout>