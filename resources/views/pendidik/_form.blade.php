{{-- ============================================================ --}}
{{-- resources/views/pendidik/_form.blade.php                    --}}
{{-- Shared form untuk create dan edit                           --}}
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

    {{-- === SECTION 1: Akun & Role === --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Akun & Role</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Foto --}}
            <div class="md:col-span-2 flex items-center gap-5">
                <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center
                            justify-center border-2 border-dashed border-gray-200"
                    id="foto-preview"
                    style="{{ $pendidik?->foto ? 'border-style: solid;' : '' }}">
                    @if($pendidik?->foto)
                    <img src="{{ Storage::url($pendidik->foto) }}" class="w-full h-full object-cover">
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
                        Foto
                    </label>
                    <input type="file" name="foto" accept="image/*"
                        onchange="previewFoto(this)"
                        class="text-sm text-siakad-secondary
                                  file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                                  file:text-sm file:font-medium file:text-white file:transition"
                        style="--file-btn-bg: var(--siakad-primary);">
                    <p class="text-xs text-siakad-secondary mt-1">
                        JPG/PNG/WebP · Maks 2MB
                    </p>
                </div>
            </div>

            {{-- Nama --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                    value="{{ old('name', $pendidik?->user?->name) }}"
                    placeholder="Nama lengkap pendidik" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              @error('name') border-red-400 bg-red-50
                              @else border-gray-200 bg-gray-50 @enderror
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 focus:border-transparent outline-none transition">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email"
                    value="{{ old('email', $pendidik?->user?->email) }}"
                    placeholder="email@alislam.sch.id" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              @error('email') border-red-400 bg-red-50
                              @else border-gray-200 bg-gray-50 @enderror
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 focus:border-transparent outline-none transition">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role" required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200 bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    <option value="">-- Pilih Role --</option>
                    @foreach([
                    'guru' => 'Guru',
                    'guru_tahsin_tahfizh' => 'Guru Tahsin-Tahfizh',
                    'wakil_kurikulum' => 'Wakil Kurikulum',
                    'kesantrian' => 'Bagian Kesantrian',
                    'admin' => 'Staf Admin',
                    'mudir' => 'Mudir Pondok',
                    ] as $val => $label)
                    <option value="{{ $val }}"
                        @selected(old('role', $pendidik?->user?->role) === $val)>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Password
                    @if($pendidik)
                    <span class="text-xs font-normal text-siakad-secondary ml-1">
                        (kosongkan jika tidak ingin mengubah)
                    </span>
                    @else
                    <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="password" name="password"
                    placeholder="{{ $pendidik ? 'Isi untuk mengubah password' : 'Minimal 8 karakter' }}"
                    @if(!$pendidik) required @endif
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              @error('password') border-red-400 bg-red-50
                              @else border-gray-200 bg-gray-50 @enderror
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>
    </div>

    {{-- === SECTION 2: Data Kepegawaian === --}}
    <div class="card-saas overflow-hidden">
        <div class="px-5 py-3.5 flex items-center gap-2"
            style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
            <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
            <h3 class="font-semibold text-sm text-siakad-dark">Data Kepegawaian</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- NIP --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">NIP</label>
                <input type="text" name="nip"
                    value="{{ old('nip', $pendidik?->nip) }}"
                    placeholder="Nomor Induk Pegawai"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- NIK --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">NIK</label>
                <input type="text" name="nik"
                    value="{{ old('nik', $pendidik?->nik) }}"
                    placeholder="16 digit NIK KTP"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
                @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Jenis Kelamin --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Jenis Kelamin
                </label>
                <select name="jenis_kelamin"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200 bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    <option value="">-- Pilih --</option>
                    <option value="L" @selected(old('jenis_kelamin', $pendidik?->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $pendidik?->jenis_kelamin) === 'P')>Perempuan</option>
                </select>
            </div>

            {{-- No HP --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">No. HP</label>
                <input type="tel" name="no_hp"
                    value="{{ old('no_hp', $pendidik?->no_hp) }}"
                    placeholder="08xxxxxxxxxx"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>

            {{-- Tempat Lahir --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Tempat Lahir
                </label>
                <input type="text" name="tempat_lahir"
                    value="{{ old('tempat_lahir', $pendidik?->tempat_lahir) }}"
                    placeholder="Kota/Kabupaten"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>

            {{-- Tanggal Lahir --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Tanggal Lahir
                </label>
                <input type="date" name="tanggal_lahir"
                    value="{{ old('tanggal_lahir', $pendidik?->tanggal_lahir?->format('Y-m-d')) }}"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark focus:ring-2 outline-none transition">
            </div>

            {{-- Pendidikan Terakhir --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Pendidikan Terakhir
                </label>
                <select name="pendidikan_terakhir"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200 bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    <option value="">-- Pilih --</option>
                    @foreach(['SMA/SMK/MA','D3','S1','S2','S3'] as $pd)
                    <option value="{{ $pd }}"
                        @selected(old('pendidikan_terakhir', $pendidik?->pendidikan_terakhir) === $pd)>
                        {{ $pd }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Jurusan --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Jurusan
                </label>
                <input type="text" name="jurusan"
                    value="{{ old('jurusan', $pendidik?->jurusan) }}"
                    placeholder="e.g. Pendidikan Bahasa Arab"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark placeholder-gray-400
                              focus:ring-2 outline-none transition">
            </div>

            {{-- Status Kepegawaian --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Status Kepegawaian
                </label>
                <select name="status_kepegawaian"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                               border-gray-200 bg-gray-50
                               text-siakad-dark focus:ring-2 outline-none transition">
                    @foreach(['tetap'=>'Tetap','kontrak'=>'Kontrak','honorer'=>'Honorer'] as $val => $label)
                    <option value="{{ $val }}"
                        @selected(old('status_kepegawaian', $pendidik?->status_kepegawaian) === $val)>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tanggal Masuk --}}
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                    Tanggal Masuk
                </label>
                <input type="date" name="tanggal_masuk"
                    value="{{ old('tanggal_masuk', $pendidik?->tanggal_masuk?->format('Y-m-d')) }}"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 bg-gray-50
                              text-siakad-dark focus:ring-2 outline-none transition">
            </div>

            {{-- Alamat --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">Alamat</label>
                <textarea name="alamat" rows="2"
                    placeholder="Alamat lengkap"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                                 border-gray-200 bg-gray-50
                                 text-siakad-dark placeholder-gray-400
                                 focus:ring-2 outline-none resize-none transition">{{ old('alamat', $pendidik?->alamat) }}</textarea>
            </div>

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
        <a href="{{ route('admin.pendidik.index') }}"
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