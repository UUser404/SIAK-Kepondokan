{{-- ============================================================ --}}
{{-- resources/views/santri/_form.blade.php                      --}}
{{-- ============================================================ --}}
<div class="space-y-6 max-w-4xl">

    {{-- Error summary --}}
    @if($errors->any())
    <div class="px-4 py-3 rounded-xl text-sm"
        style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
        <p class="font-medium mb-1">Terdapat {{ $errors->count() }} kesalahan:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- === SECTION 1: Identitas === --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Identitas Santri</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Foto --}}
            <div class="md:col-span-2 flex items-center gap-5">
                <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center
                            justify-center border-2 border-dashed border-gray-200"
                    id="foto-preview"
                    style="{{ $santri?->foto ? 'border-style: solid;' : '' }}">
                    @if($santri?->foto)
                    <img src="{{ Storage::url($santri->foto) }}" class="w-full h-full object-cover">
                    @else
                    <svg class="w-8 h-8 text-gray-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1">
                        Foto Santri
                    </label>
                    <input type="file" name="foto" accept="image/*"
                        onchange="previewFoto(this)"
                        class="text-sm text-siakad-secondary
                                  file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                                  file:text-sm file:font-medium file:transition
                                  file:text-white"
                        style="--file-btn-bg: var(--siakad-primary);">
                    <p class="text-xs text-siakad-secondary mt-1">
                        JPG/PNG/WebP · Maks 2MB
                    </p>
                </div>
            </div>

            @php
            $fields = [
            ['name'=>'nis', 'label'=>'NIS', 'required'=>true, 'type'=>'text', 'placeholder'=>'Nomor Induk Santri', 'col'=>1],
            ['name'=>'nisn', 'label'=>'NISN', 'required'=>false, 'type'=>'text', 'placeholder'=>'10 digit (opsional)', 'col'=>1],
            ['name'=>'nama_lengkap', 'label'=>'Nama Lengkap', 'required'=>true, 'type'=>'text', 'placeholder'=>'Nama sesuai akta lahir','col'=>2],
            ['name'=>'nama_panggilan','label'=>'Nama Panggilan', 'required'=>false, 'type'=>'text', 'placeholder'=>'Nama sehari-hari', 'col'=>1],
            ['name'=>'tempat_lahir', 'label'=>'Tempat Lahir', 'required'=>false, 'type'=>'text', 'placeholder'=>'Kota/Kabupaten', 'col'=>1],
            ['name'=>'tanggal_lahir', 'label'=>'Tanggal Lahir', 'required'=>false, 'type'=>'date', 'placeholder'=>'', 'col'=>1],
            ['name'=>'asal_sekolah', 'label'=>'Asal Sekolah', 'required'=>false, 'type'=>'text', 'placeholder'=>'Nama sekolah asal', 'col'=>1],
            ['name'=>'angkatan', 'label'=>'Angkatan', 'required'=>false, 'type'=>'number', 'placeholder'=>now()->year, 'col'=>1],
            ['name'=>'no_hp_santri', 'label'=>'No. HP Santri', 'required'=>false, 'type'=>'tel', 'placeholder'=>'081234567890', 'col'=>1],
            ];
            @endphp

            @foreach($fields as $f)
            <div @class(['md:col-span-2'=> $f['col'] === 2])>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    {{ $f['label'] }}
                    @if($f['required']) <span class="text-red-500">*</span> @endif
                </label>
                <input type="{{ $f['type'] }}"
                    name="{{ $f['name'] }}"
                    value="{{ old($f['name'], $santri?->{$f['name']} instanceof \Carbon\Carbon
                           ? $santri->{$f['name']}->format('Y-m-d')
                           : $santri?->{$f['name']}) }}"
                    placeholder="{{ $f['placeholder'] }}"
                    @if($f['required']) required @endif
                    @if($f['name']==='angkatan' ) min="2000" max="{{ now()->year + 1 }}" @endif
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              @error($f['name']) border-red-400 bg-red-50
                              @else border-gray-200 bg-gray-50 @enderror
                              text-siakad-dark
                              placeholder-gray-400
                              focus:ring-2 focus:border-transparent outline-none transition">
                @error($f['name'])
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endforeach

            {{-- Jenis Kelamin --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <select name="jenis_kelamin" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200
                               bg-gray-50
                               text-siakad-dark
                               focus:ring-2 focus:border-transparent outline-none transition">
                    <option value="">-- Pilih --</option>
                    <option value="L" @selected(old('jenis_kelamin', $santri?->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $santri?->jenis_kelamin) === 'P')>Perempuan</option>
                </select>
            </div>

            {{-- Alamat --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">Alamat</label>
                <textarea name="alamat" rows="2"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                                 border-gray-200
                                 bg-gray-50
                                 text-siakad-dark
                                 placeholder-gray-400
                                 focus:ring-2 focus:border-transparent outline-none transition resize-none"
                    placeholder="Alamat lengkap santri">{{ old('alamat', $santri?->alamat) }}</textarea>
            </div>
        </div>
    </div>

    {{-- === SECTION 2: Data Wali === --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Data Orang Tua / Wali</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach([
            ['nama_ayah', 'Nama Ayah', 1],
            ['nama_ibu', 'Nama Ibu', 1],
            ['nama_wali', 'Nama Wali', 2],
            ['no_hp_wali', 'No. HP Wali', 1],
            ['pekerjaan_wali', 'Pekerjaan Wali', 1],
            ] as [$field, $label, $col])
            <div @class(['md:col-span-2'=> $col === 2])>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    {{ $label }}
                </label>
                <input type="{{ $field === 'no_hp_wali' ? 'tel' : 'text' }}"
                    name="{{ $field }}"
                    value="{{ old($field, $santri?->$field) }}"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200
                              bg-gray-50
                              text-siakad-dark
                              placeholder-gray-400
                              focus:ring-2 focus:border-transparent outline-none transition">
            </div>
            @endforeach
        </div>
    </div>

    {{-- === SECTION 3: Penempatan Kelas === --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Penempatan Kelas</h3>
        </div>
        <div class="p-5">
            <div class="max-w-sm">
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Kelas
                </label>
                <select name="kelas_id"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200
                               bg-gray-50
                               text-siakad-dark
                               focus:ring-2 focus:border-transparent outline-none transition">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}"
                        @selected(old('kelas_id', $kelasAktif?->id ?? '') == $kelas->id)>
                        {{ $kelas->nama }} — {{ $kelas->tingkatan->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($ta)
            <input type="hidden" name="tahun_ajaran_id" value="{{ $ta->id }}">
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button type="submit"
            class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2
                       focus:ring-offset-2"
            style="background-color: var(--siakad-primary);
                       --tw-ring-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.santri.index') }}"
            class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition-colors">
            Batal
        </a>
    </div>

</div>

@push('scripts')
<script>
    function previewFoto(input) {
        const preview = document.getElementById('foto-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                preview.style.borderStyle = 'solid';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush