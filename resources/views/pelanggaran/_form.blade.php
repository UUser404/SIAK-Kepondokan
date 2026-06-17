{{-- ============================================================ --}}
{{-- resources/views/pelanggaran/_form.blade.php                 --}}
{{-- ============================================================ --}}
<div class="card-saas overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full bg-red-500"></div>
        <h3 class="font-semibold text-sm text-siakad-dark">Data Pelanggaran</h3>
    </div>
    <div class="p-5 space-y-5">

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Santri --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Santri <span class="text-red-500">*</span>
            </label>
            <select name="santri_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50
                           text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">-- Pilih Santri --</option>
                @foreach($santriList as $s)
                <option value="{{ $s->id }}"
                    @selected(old('santri_id', $pelanggaran?->santri_id) == $s->id)>
                    {{ $s->nama_lengkap }} ({{ $s->nis }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Kategori berdasar tingkat --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Kategori Pelanggaran <span class="text-red-500">*</span>
            </label>
            <select name="kategori_pelanggaran_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50
                           text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoriList as $tingkat => $kategori)
                <optgroup label="{{ ucfirst($tingkat) }}">
                    @foreach($kategori as $k)
                    <option value="{{ $k->id }}"
                        @selected(old('kategori_pelanggaran_id', $pelanggaran?->kategori_pelanggaran_id) == $k->id)>
                        {{ $k->nama }} ({{ $k->poin }} poin)
                    </option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
        </div>

        {{-- Tanggal --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <input type="date" name="tanggal"
                value="{{ old('tanggal', $pelanggaran?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                max="{{ today()->format('Y-m-d') }}" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-siakad-dark focus:ring-2 outline-none transition">
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Deskripsi Kejadian <span class="text-red-500">*</span>
            </label>
            <textarea name="deskripsi" rows="3" required
                placeholder="Uraikan kejadian pelanggaran secara singkat..."
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                             bg-gray-50
                             text-siakad-dark placeholder-gray-400
                             focus:ring-2 outline-none resize-none transition">{{ old('deskripsi', $pelanggaran?->deskripsi) }}</textarea>
        </div>

        {{-- Sanksi --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Sanksi
            </label>
            <input type="text" name="sanksi"
                value="{{ old('sanksi', $pelanggaran?->sanksi) }}"
                placeholder="e.g. Membersihkan halaman selama 3 hari"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                           hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('kesantrian.pelanggaran.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary
                      hover:text-siakad-dark transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>