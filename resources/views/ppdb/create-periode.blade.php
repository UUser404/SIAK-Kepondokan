{{-- ============================================================ --}}
{{-- resources/views/ppdb/create-periode.blade.php                --}}
{{-- File ini sebelumnya belum ada sama sekali, padahal sudah     --}}
{{-- dipanggil oleh PpdbController::createPeriode()                --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Buat Periode PPDB</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.ppdb.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Buat Periode PPDB Baru</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Tentukan jadwal, kuota, dan persyaratan pendaftaran santri baru
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.ppdb.store-periode') }}" class="max-w-2xl">
        @csrf

        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Data Periode PPDB</h3>
            </div>
            <div class="p-5 space-y-5">

                @if($errors->any())
                <div class="px-4 py-3 rounded-xl text-sm"
                    style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Nama Periode --}}
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Nama Periode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        placeholder="e.g. PPDB Tahun Ajaran 2026/2027"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                  bg-gray-50
                                  text-siakad-dark placeholder-gray-400
                                  focus:ring-2 outline-none transition">
                </div>

                {{-- Tahun Ajaran --}}
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Tahun Ajaran <span class="text-red-500">*</span>
                    </label>
                    <select name="tahun_ajaran_id" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                   bg-gray-50
                                   text-siakad-dark focus:ring-2 outline-none transition">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($tahunAjaranList as $ta)
                        <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id')==$ta->id)>
                            {{ $ta->nama }}
                        </option>
                        @endforeach
                    </select>
                    @if($tahunAjaranList->isEmpty())
                    <p class="text-xs text-red-500 mt-1.5">
                        Belum ada data tahun ajaran. Tambahkan tahun ajaran terlebih dahulu di menu Data Master.
                    </p>
                    @endif
                </div>

                {{-- Tanggal Buka & Tutup --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Tanggal Buka <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_buka" value="{{ old('tanggal_buka') }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                      bg-gray-50
                                      text-siakad-dark focus:ring-2 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Tanggal Tutup <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_tutup" value="{{ old('tanggal_tutup') }}" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                      bg-gray-50
                                      text-siakad-dark focus:ring-2 outline-none transition">
                    </div>
                </div>

                {{-- Kuota --}}
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Kuota Pendaftar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="kuota" min="1" value="{{ old('kuota', 100) }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                  bg-gray-50
                                  text-siakad-dark focus:ring-2 outline-none transition">
                </div>

                {{-- Persyaratan --}}
                <div>
                    <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                        Persyaratan Pendaftaran
                    </label>
                    <textarea name="persyaratan" rows="4"
                        placeholder="e.g. Fotokopi akta kelahiran, ijazah/rapor terakhir, pas foto 3x4..."
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                     bg-gray-50
                                     text-siakad-dark placeholder-gray-400
                                     focus:ring-2 outline-none resize-none transition">{{ old('persyaratan') }}</textarea>
                    <p class="text-xs text-siakad-secondary mt-1.5">
                        Opsional. Akan ditampilkan di halaman pendaftaran publik.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                                   hover:-translate-y-0.5 hover:shadow-lg"
                        style="background-color: var(--siakad-primary);"
                        onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                        onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                        Simpan Periode
                    </button>
                    <a href="{{ route('admin.ppdb.index') }}"
                        class="px-4 py-2.5 text-sm text-siakad-secondary
                              hover:text-siakad-dark transition-colors">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>