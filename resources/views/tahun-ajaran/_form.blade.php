{{-- ============================================================ --}}
{{-- resources/views/tahun-ajaran/_form.blade.php                --}}
{{-- ============================================================ --}}
<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Tahun Ajaran</h3>
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
                Nama Tahun Ajaran <span class="text-red-500">*</span>
                <span class="text-xs font-normal text-siakad-secondary ml-1">(format: 2025/2026)</span>
            </label>
            <input type="text" name="nama"
                value="{{ old('nama', $tahunAjaran?->nama) }}"
                placeholder="2025/2026" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Semester <span class="text-red-500">*</span>
            </label>
            <select name="semester" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Semester --</option>
                <option value="ganjil" @selected(old('semester', $tahunAjaran?->semester) === 'ganjil')>Ganjil</option>
                <option value="genap" @selected(old('semester', $tahunAjaran?->semester) === 'genap')>Genap</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_mulai"
                    value="{{ old('tanggal_mulai', $tahunAjaran?->tanggal_mulai?->format('Y-m-d')) }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Tanggal Selesai <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_selesai"
                    value="{{ old('tanggal_selesai', $tahunAjaran?->tanggal_selesai?->format('Y-m-d')) }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Tanggal Cetak Rapor (Hijriah)
            </label>
            <input type="text" name="tanggal_rapor_hijriah" dir="rtl"
                value="{{ old('tanggal_rapor_hijriah', $tahunAjaran?->tanggal_rapor_hijriah) }}"
                placeholder="مثال: 02 رجب 1447"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">
                Diisi manual (bukan konversi otomatis) — dipakai di kop Rapor Arab, ditampilkan berdampingan dengan Tanggal Selesai (Masehi).
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Nama Kepala Sekolah (Arab)
                </label>
                <input type="text" name="nama_kepala_sekolah_arab" dir="rtl"
                    value="{{ old('nama_kepala_sekolah_arab', $tahunAjaran?->nama_kepala_sekolah_arab) }}"
                    placeholder="رامي صالح الدين معروف"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Nama Mudir Ma'had (Arab)
                </label>
                <input type="text" name="nama_mudir_arab" dir="rtl"
                    value="{{ old('nama_mudir_arab', $tahunAjaran?->nama_mudir_arab) }}"
                    placeholder="محمد إبراهيم"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>
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
            <a href="{{ route('admin.tahun-ajaran.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>