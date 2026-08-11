{{-- resources/views/mata-pelajaran/_form.blade.php --}}
<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Mata Pelajaran</h3>
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

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Kode <span class="text-red-500">*</span>
            </label>
            <input type="text" name="kode"
                value="{{ old('kode', $mataPelajaran?->kode) }}"
                placeholder="e.g. MAT" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border uppercase
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            @error('kode')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Nama Mata Pelajaran <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama"
                value="{{ old('nama', $mataPelajaran?->nama) }}"
                placeholder="e.g. Matematika" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Kategori (pengelompokan di Rapor)
            </label>
            <select name="kategori"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Belum dikelompokkan --</option>
                @foreach($kategoriMataPelajaran as $kat)
                <option value="{{ $kat->nama }}"
                    {{ old('kategori', $mataPelajaran?->kategori) === $kat->nama ? 'selected' : '' }}>
                    {{ $kat->nama }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                Mapel dikelompokkan di rapor berdasarkan kategori ini — mapel tanpa kategori tidak akan tampil di rapor.
                <a href="{{ route('admin.kategori-mata-pelajaran.index') }}" class="underline hover:text-siakad-primary">Kelola daftar kategori</a>.
            </p>
            @error('kategori')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Tingkat <span class="text-red-500">*</span>
            </label>
            <select name="tingkat" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Tingkat --</option>
                @foreach(['7','8','9','10','11','12'] as $t)
                <option value="{{ $t }}"
                    {{ old('tingkat', $mataPelajaran?->tingkat) == $t ? 'selected' : '' }}>
                    Kelas {{ $t }}
                </option>
                @endforeach
            </select>
            @error('tingkat')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
                             focus:ring-2 outline-none resize-none transition">{{ old('deskripsi', $mataPelajaran?->deskripsi) }}</textarea>
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
            <a href="{{ route('admin.mata-pelajaran.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>