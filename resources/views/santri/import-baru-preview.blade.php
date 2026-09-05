{{-- ============================================================ --}}
{{-- resources/views/santri/import-baru-preview.blade.php          --}}
{{-- Preview santri BARU yang akan dibuat -- setiap baris bisa      --}}
{{-- di-approve/skip satu per satu, atau pakai tombol massal.       --}}
{{-- Baris yang errors-nya tidak kosong TIDAK BISA dipaksa approve  --}}
{{-- (beda dari import-preview.blade.php) -- lihat catatan di       --}}
{{-- SantriCreateService::save().                                   --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Preview Santri Baru</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.import-baru') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Preview Santri Baru</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Periksa dulu sebelum disimpan — belum ada santri yang dibuat sama sekali
            </p>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Baris</p>
            <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ $preview['summary']['total'] }}</p>
        </div>
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Valid</p>
            <p class="text-2xl font-bold mt-1" style="color:#15803d;">{{ $preview['summary']['valid'] }}</p>
        </div>
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Error</p>
            <p class="text-2xl font-bold mt-1" style="color:#dc2626;">{{ $preview['summary']['errors'] }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.santri.import-baru.store') }}">
        @csrf

        {{-- Tombol aksi massal -- baris error tetap ke-skip otomatis di
             backend walau di-set "Setujui" di sini, lihat SantriCreateService::save(). --}}
        <div class="flex items-center gap-3 mb-3">
            <button type="button" onclick="setSemuaAksiBaru('approve')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700
                       text-siakad-dark dark:text-white
                       hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Setujui Semua
            </button>
            <button type="button" onclick="setSemuaAksiBaru('skip')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700
                       text-siakad-secondary dark:text-gray-400
                       hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Lewati Semua
            </button>
        </div>

        <div class="card-saas dark:bg-gray-800 overflow-hidden mb-5">
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Baris','NISN','Nama Lengkap','Kelas','Angkatan','L/P','Catatan','Aksi'] as $h)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary dark:text-gray-400">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700" style="border-color: var(--border-color);">
                        @foreach($preview['records'] as $index => $r)
                        <tr class="dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-siakad-secondary dark:text-gray-400">{{ $r['row_number'] }}</td>
                            <td class="px-4 py-3 text-siakad-dark dark:text-white font-medium">{{ $r['nisn'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-siakad-dark dark:text-white">{{ $r['nama_lengkap'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-siakad-dark dark:text-white">{{ $r['kelas_nama'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-siakad-secondary dark:text-gray-400">{{ $r['angkatan'] ?: '-' }}</td>
                            <td class="px-4 py-3 text-siakad-secondary dark:text-gray-400">{{ $r['jenis_kelamin'] ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($r['errors']))
                                @foreach($r['errors'] as $err)
                                <p class="text-xs" style="color:#dc2626;">{{ $err }}</p>
                                @endforeach
                                @else
                                <span class="text-xs text-siakad-secondary dark:text-gray-400">Siap dibuat</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(!empty($r['errors']))
                                <select name="action_{{ $index }}" disabled
                                    class="px-2.5 py-1.5 text-xs rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800
                                          text-siakad-secondary dark:text-gray-400">
                                    <option value="skip" selected>Dilewati (error)</option>
                                </select>
                                {{-- select disabled tidak ikut ter-submit -- kirim value skip via hidden input --}}
                                <input type="hidden" name="action_{{ $index }}" value="skip">
                                @else
                                <select name="action_{{ $index }}"
                                    class="px-2.5 py-1.5 text-xs rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none">
                                    <option value="approve" selected>Setujui</option>
                                    <option value="skip">Lewati</option>
                                </select>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white
                       transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                Buat Santri
            </button>
            <a href="{{ route('admin.santri.import-baru') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal, upload ulang
            </a>
        </div>
    </form>

    <script>
        function setSemuaAksiBaru(aksi) {
            // Cuma dropdown yang aktif (baris tanpa error) yang boleh diubah --
            // dropdown yang disabled (baris error) sudah dikunci "skip" lewat
            // hidden input, tidak perlu (dan tidak bisa) diubah dari sini.
            document.querySelectorAll('select[name^="action_"]:not([disabled])').forEach(function(select) {
                select.value = aksi;
            });
        }
    </script>
</x-app-layout>