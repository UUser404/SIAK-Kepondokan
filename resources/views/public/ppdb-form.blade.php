{{-- ============================================================ --}}
{{-- resources/views/public/ppdb-form.blade.php                  --}}
{{-- Form pendaftaran publik                                     --}}
{{-- ============================================================ --}}
<x-guest-layout>
    <x-slot name="title">Formulir Pendaftaran — PPDB</x-slot>

    <div class="mb-8">
        <a href="{{ route('ppdb.public.index') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
            ← Kembali
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Formulir Pendaftaran</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $periode->nama }}</p>
    </div>

    @if($errors->any())
    <div class="mb-5 px-4 py-3 rounded-xl text-sm"
        style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('ppdb.public.store') }}" class="space-y-6">
        @csrf

        {{-- Data Calon Santri --}}
        <div>
            <h3 class="font-semibold text-gray-800 text-sm mb-3 pb-2
               border-b border-gray-100">
                Data Calon Santri
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                        placeholder="Nama sesuai akta lahir"
                        class="block w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-gray-900 placeholder-gray-400
                          focus:ring-2 focus:border-transparent outline-none transition
                          @error('nama_lengkap') border-red-400 @enderror">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_kelamin" required
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                               bg-gray-50
                               text-gray-900 focus:ring-2 outline-none">
                            <option value="">-- Pilih --</option>
                            <option value="L" @selected(old('jenis_kelamin')==='L' )>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin')==='P' )>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-gray-900 focus:ring-2 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tempat Lahir
                        </label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-gray-900 focus:ring-2 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            NISN
                        </label>
                        <input type="text" name="nisn" value="{{ old('nisn') }}" placeholder="10 digit"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-gray-900 focus:ring-2 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Asal Sekolah
                    </label>
                    <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}"
                        placeholder="Nama sekolah asal"
                        class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-gray-900 placeholder-gray-400
                          focus:ring-2 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat
                    </label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap"
                        class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                             bg-gray-50
                             text-gray-900 placeholder-gray-400
                             focus:ring-2 outline-none resize-none">{{ old('alamat') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Data Orang Tua / Wali --}}
        <div>
            <h3 class="font-semibold text-gray-800 text-sm mb-3 pb-2
               border-b border-gray-100">
                Data Orang Tua / Wali
            </h3>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Ayah
                        </label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-gray-900 focus:ring-2 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Ibu
                        </label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                              bg-gray-50
                              text-gray-900 focus:ring-2 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        No. HP Wali <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="no_hp_wali" value="{{ old('no_hp_wali') }}" required
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-gray-900 focus:ring-2 outline-none
                          @error('no_hp_wali') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email Wali
                    </label>
                    <input type="email" name="email_wali" value="{{ old('email_wali') }}"
                        placeholder="email@contoh.com"
                        class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200
                          bg-gray-50
                          text-gray-900 focus:ring-2 outline-none">
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full py-3.5 text-sm font-semibold rounded-xl text-white transition-all
               hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            Kirim Pendaftaran
        </button>

        <p class="text-xs text-center text-gray-400">
            Dengan mendaftar, Anda menyetujui bahwa data yang diberikan adalah benar.
        </p>
    </form>
</x-guest-layout>