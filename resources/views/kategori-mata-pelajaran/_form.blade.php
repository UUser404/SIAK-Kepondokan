{{-- resources/views/kategori-mata-pelajaran/_form.blade.php --}}
<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Kategori Mata Pelajaran</h3>
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
                Nama Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama"
                value="{{ old('nama', $kategoriMataPelajaran?->nama) }}"
                placeholder="e.g. القرآن و علومه" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                Kategori ini akan otomatis muncul sebagai pilihan di form Mata Pelajaran.
                Kalau kamu ubah nama di sini, semua mapel yang sudah pakai kategori ini
                ikut diperbarui otomatis.
            </p>
            @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Urutan Tampil
            </label>
            <input type="number" name="urutan" min="1"
                value="{{ old('urutan', $kategoriMataPelajaran?->urutan) }}"
                placeholder="e.g. 1"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                Menentukan urutan kategori di dropdown & pengelompokan rapor. Boleh dikosongkan.
            </p>
            @error('urutan')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
            <a href="{{ route('admin.kategori-mata-pelajaran.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>