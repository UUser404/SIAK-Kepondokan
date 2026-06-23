<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Kelas</h3>
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
                Nama Kelas <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama"
                value="{{ old('nama', $kelas?->nama) }}"
                placeholder="e.g. 7A" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
            @error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Tingkatan <span class="text-red-500">*</span>
            </label>
            <select name="tingkatan_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Tingkatan --</option>
                @foreach($tingkatanList as $t)
                <option value="{{ $t->id }}"
                    @selected(old('tingkatan_id', $kelas?->tingkatan_id) == $t->id)>
                    {{ $t->nama }}
                </option>
                @endforeach
            </select>
            @error('tingkatan_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Wali Kelas
            </label>
            <select name="wali_kelas_id"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Belum Ditentukan --</option>
                @foreach($guruList as $g)
                <option value="{{ $g->id }}"
                    @selected(old('wali_kelas_id', $kelas?->wali_kelas_id) == $g->id)>
                    {{ $g->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Kapasitas <span class="text-red-500">*</span>
            </label>
            <input type="number" name="kapasitas" min="1" max="60"
                value="{{ old('kapasitas', $kelas?->kapasitas ?? 30) }}" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
            @error('kapasitas')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
            <a href="{{ route('kurikulum.kelas.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>