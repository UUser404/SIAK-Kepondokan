{{-- ============================================================ --}}
{{-- resources/views/santri/import-baru.blade.php                  --}}
{{-- Form upload untuk fitur Import Santri Baru (MEMBUAT santri     --}}
{{-- baru, beda dari import-bulk.blade.php yang meng-UPDATE kelas/  --}}
{{-- asrama santri yang sudah ada).                                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Import Santri Baru</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Import Santri Baru</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Untuk memasukkan data santri dalam jumlah banyak sekaligus (mis. migrasi data awal)
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card-saas dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                            background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Upload File</h3>
                </div>
                <div class="p-5">
                    @if($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-xl text-sm"
                        style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.santri.import-baru.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-200 mb-1.5">
                            File Excel (.xlsx, .xls, .csv) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border
                                  border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                  text-siakad-dark dark:text-white
                                  file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold file:text-white
                                  focus:ring-2 outline-none transition"
                            style="--tw-file-bg: var(--siakad-primary);">
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-2">
                            Kolom wajib semua: <strong>NISN, Nama Lengkap, Kelas, Angkatan, Jenis Kelamin</strong>.
                            Kelas harus rombel spesifik (mis. "7A"), bukan cuma level ("Kelas 7") -- pastikan
                            sudah dibuat dulu di menu Kelas. Jenis Kelamin bisa "L"/"P" atau "Laki-laki"/"Perempuan".
                        </p>

                        <button type="submit"
                            class="mt-5 px-6 py-2.5 text-sm font-semibold rounded-xl text-white
                                   transition-all hover:-translate-y-0.5 hover:shadow-lg"
                            style="background-color: var(--siakad-primary);"
                            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                            Lanjut ke Preview
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div class="card-saas dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-3.5"
                    style="border-bottom: 1px solid var(--border-color);
                            background-color: rgba(35,76,106,0.04);">
                    <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Cara Pakai</h3>
                </div>
                <div class="p-5 space-y-3 text-sm text-siakad-secondary dark:text-gray-400">
                    <p>1. Download template di bawah, isi 5 kolom yang wajib semua.</p>
                    <p>2. Pastikan <strong>NISN</strong> belum pernah terdaftar sebelumnya di sistem (kalau sudah ada, baris itu otomatis dilewati saat preview).</p>
                    <p>3. Pastikan <strong>Kelas</strong> (rombel spesifik, mis. "7A") sudah ada di menu Kelas untuk tahun ajaran aktif.</p>
                    <p>4. Upload — sistem tampilkan preview dulu, termasuk baris mana yang error, sebelum benar-benar disimpan.</p>
                    <p>5. Di halaman preview, bisa pilih baris mana yang disetujui/dilewati, atau pakai "Setujui Semua".</p>

                    <a href="{{ route('admin.santri.import-baru-template') }}"
                        class="inline-flex items-center gap-2 mt-2 px-4 py-2 text-sm font-medium rounded-xl border
                               border-gray-200 dark:border-gray-700 text-siakad-dark dark:text-white
                               hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>