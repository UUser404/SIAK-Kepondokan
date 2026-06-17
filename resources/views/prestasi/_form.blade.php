{{-- ============================================================ --}}
{{-- resources/views/prestasi/_form.blade.php (shared)          --}}
{{-- ============================================================ --}}
{{-- Dipakai oleh create.blade.php dan edit.blade.php            --}}
<div class="card-saas overflow-hidden max-w-2xl">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full bg-yellow-500"></div>
        <h3 class="font-semibold text-sm text-siakad-dark">Data Prestasi</h3>
    </div>
    <div class="p-5 space-y-5">

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
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
                    @selected(old('santri_id', $prestasi?->santri_id) == $s->id)>
                    {{ $s->nama_lengkap }} ({{ $s->nis }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Nama prestasi --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Nama Prestasi <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama_prestasi"
                value="{{ old('nama_prestasi', $prestasi?->nama_prestasi) }}"
                placeholder="e.g. Juara 1 Musabaqah Tilawatil Quran Tingkat Kabupaten"
                required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        <div class="grid grid-cols-2 gap-4">
            {{-- Jenis --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Jenis <span class="text-red-500">*</span>
                </label>
                <select name="jenis" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    @foreach(['akademik'=>'Akademik','non_akademik'=>'Non Akademik','hafalan'=>'Hafalan','lainnya'=>'Lainnya'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('jenis', $prestasi?->jenis) === $val)>
                        {{ $lbl }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tingkat --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Tingkat <span class="text-red-500">*</span>
                </label>
                <select name="tingkat" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    @foreach(['pondok','kecamatan','kabupaten','provinsi','nasional','internasional'] as $t)
                    <option value="{{ $t }}" @selected(old('tingkat', $prestasi?->tingkat) === $t)>
                        {{ ucfirst($t) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Peringkat --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Peringkat
                </label>
                <select name="peringkat"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    <option value="">-- Pilih (opsional) --</option>
                    @foreach(['juara_1'=>'Juara 1','juara_2'=>'Juara 2','juara_3'=>'Juara 3','harapan'=>'Harapan','peserta'=>'Peserta','lainnya'=>'Lainnya'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('peringkat', $prestasi?->peringkat) === $val)>
                        {{ $lbl }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal"
                    value="{{ old('tanggal', $prestasi?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-siakad-dark focus:ring-2 outline-none transition">
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Keterangan
            </label>
            <textarea name="keterangan" rows="2"
                placeholder="Keterangan tambahan..."
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                             bg-gray-50
                             text-siakad-dark placeholder-gray-400
                             focus:ring-2 outline-none resize-none transition">{{ old('keterangan', $prestasi?->keterangan) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                           hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('kesantrian.prestasi.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary
                      hover:text-siakad-dark transition-colors">
                Batal
            </a>
        </div>

    </div>
</div>