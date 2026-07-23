<div class="card-saas dark:bg-gray-800 overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
        <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Detail Jadwal</h3>
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

        @if(session('error'))
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;">
            {{ session('error') }}
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Kelas <span class="text-red-500">*</span>
            </label>
            <select name="kelas_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}"
                    @selected(old('kelas_id', $jadwal?->kelas_id) == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            @error('kelas_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Mata Pelajaran <span class="text-red-500">*</span>
            </label>
            <select name="mata_pelajaran_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Mapel --</option>
                {{-- Mengubah variabel menjadi $mataPelajaran sesuai compact controller lama --}}
                @foreach($mataPelajaran as $m)
                <option value="{{ $m->id }}"
                    @selected(old('mata_pelajaran_id', $jadwal?->mata_pelajaran_id) == $m->id)>{{ $m->nama }}</option>
                @endforeach
            </select>
            @error('mata_pelajaran_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Guru Pengajar <span class="text-red-500">*</span>
            </label>
            <select name="guru_id" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Guru --</option>
                @foreach($guruList as $g)
                <option value="{{ $g->user_id }}"
                    @selected(old('guru_id', $jadwal?->guru_id) == $g->user_id)>{{ $g->user->name }}</option>
                @endforeach
            </select>
            @error('guru_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Hari <span class="text-red-500">*</span>
            </label>
            <select name="hari" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                           border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                           text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                <option value="">-- Pilih Hari --</option>
                {{-- Mengubah opsi value menjadi Huruf Kapital Awal --}}
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                <option value="{{ $h }}"
                    @selected(old('hari', $jadwal?->hari) === $h)>{{ $h }}</option>
                @endforeach
            </select>
            @error('hari')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Jam Mulai <span class="text-red-500">*</span>
                </label>
                <input type="time" name="jam_mulai"
                    value="{{ old('jam_mulai', $jadwal?->jam_mulai ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : '') }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                @error('jam_mulai')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                    Jam Selesai <span class="text-red-500">*</span>
                </label>
                <input type="time" name="jam_selesai"
                    value="{{ old('jam_selesai', $jadwal?->jam_selesai ? \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : '') }}"
                    required
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                              border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                              text-siakad-dark dark:text-white focus:ring-2 outline-none transition">
                @error('jam_selesai')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                Ruangan
            </label>
            <input type="text" name="ruangan"
                value="{{ old('ruangan', $jadwal?->ruangan) }}"
                placeholder="e.g. Ruang 7A"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                          text-siakad-dark dark:text-white placeholder-gray-400
                          focus:ring-2 outline-none transition">
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
            <a href="{{ route('kurikulum.jadwal.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>