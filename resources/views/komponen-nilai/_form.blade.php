{{-- resources/views/komponen-nilai/_form.blade.php --}}
<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Komponen Nilai</h3>
    </div>
    <div class="p-5 space-y-4">

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Mata Pelajaran --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Mata Pelajaran <span class="text-red-500">*</span>
            </label>
            <select name="mata_pelajaran_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($mataPelajaran as $mp)
                <option value="{{ $mp->id }}"
                    {{ old('mata_pelajaran_id', $komponenNilai?->mata_pelajaran_id) == $mp->id ? 'selected' : '' }}>
                    {{ $mp->nama }} (Kelas {{ $mp->tingkat }})
                </option>
                @endforeach
            </select>
            @error('mata_pelajaran_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Kode <span class="text-red-500">*</span>
                </label>
                <input type="text" name="kode"
                    value="{{ old('kode', $komponenNilai?->kode) }}"
                    placeholder="e.g. UH" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border uppercase
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white placeholder-gray-400
                              focus:ring-2 outline-none transition">
                @error('kode')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Bobot (%) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="bobot" min="0" max="100" step="0.5"
                    value="{{ old('bobot', $komponenNilai?->bobot) }}" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                @error('bobot')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Nama Komponen <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama"
                value="{{ old('nama', $komponenNilai?->nama) }}"
                placeholder="e.g. Ulangan Harian" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Maksimal Input per Semester <span class="text-red-500">*</span>
            </label>
            <input type="number" name="maks_input" min="1" max="20"
                value="{{ old('maks_input', $komponenNilai?->maks_input ?? 1) }}" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1.5">
                Berapa kali komponen ini boleh diisi per santri per semester (e.g. Tugas = 4, UH/Praktik = 2, UTS/UAS = 1).
                Guru boleh mengisi sebagian saja — nilai akhir dihitung dari rata-rata yang terisi.
            </p>
            @error('maks_input')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Tipe <span class="text-red-500">*</span>
                </label>
                <select name="tipe" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                               text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                    <option value="">-- Pilih Tipe --</option>
                    @foreach(['harian' => 'Harian', 'uts' => 'UTS', 'uas' => 'UAS', 'praktik' => 'Praktik'] as $val => $label)
                    <option value="{{ $val }}"
                        {{ old('tipe', $komponenNilai?->tipe) == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('tipe')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Urutan Tampilan
                </label>
                <input type="number" name="urutan" min="0"
                    value="{{ old('urutan', $komponenNilai?->urutan) }}"
                    placeholder="Otomatis jika kosong"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Deskripsi
            </label>
            <textarea name="deskripsi" rows="2"
                placeholder="Deskripsi singkat (opsional)"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                             border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                             text-siakad-dark dark:text-white placeholder-gray-400
                             focus:ring-2 outline-none resize-none transition">{{ old('deskripsi', $komponenNilai?->deskripsi) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white
                           transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.komponen-nilai.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>