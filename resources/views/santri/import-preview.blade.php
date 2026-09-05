{{-- ============================================================ --}}
{{-- resources/views/santri/import-preview.blade.php               --}}
{{-- Preview hasil parsing Excel sebelum benar-benar disimpan --    --}}
{{-- setiap baris bisa di-approve/skip/reject satu per satu.        --}}
{{-- --}}
{{-- PERBAIKAN: pencocokan santri sekarang pakai NISN (bukan NIS   --}}
{{-- lagi) -- kolom tabel & key array diganti dari $r['nis'] jadi   --}}
{{-- $r['nisn']. Lihat SantriImportService untuk logika lengkapnya. --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Preview Import</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.santri.import-bulk') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">Preview Import</h2>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-0.5">
                Periksa dulu sebelum disimpan — belum ada perubahan apapun ke database
            </p>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Baris</p>
            <p class="text-2xl font-bold text-siakad-dark dark:text-white mt-1">{{ $preview['summary']['total'] }}</p>
        </div>
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Valid</p>
            <p class="text-2xl font-bold mt-1" style="color:#15803d;">{{ $preview['summary']['valid'] }}</p>
        </div>
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Peringatan</p>
            <p class="text-2xl font-bold mt-1" style="color:#92400e;">{{ $preview['summary']['warning'] }}</p>
        </div>
        <div class="card-saas dark:bg-gray-800 p-4">
            <p class="text-xs text-siakad-secondary dark:text-gray-400">Error</p>
            <p class="text-2xl font-bold mt-1" style="color:#dc2626;">{{ $preview['summary']['errors'] }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.santri.import-bulk.store') }}">
        @csrf

        {{-- PERBAIKAN: tombol aksi massal -- klik sekali langsung set semua
             dropdown "Aksi" di tiap baris tanpa perlu pilih satu-satu. Aman
             dipakai walau ada baris error (santri_id null) -- backend di
             SantriImportService::save() tetap skip baris begitu kalau
             santri_id kosong, apapun nilai action-nya. --}}
        <div class="flex items-center gap-3 mb-3">
            <button type="button" onclick="setSemuaAksi('approve')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700
                       text-siakad-dark dark:text-white
                       hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Setujui Semua
            </button>
            <button type="button" onclick="setSemuaAksi('skip')"
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
                            @foreach(['Baris','NISN','Nama (Lama → Baru)','Kelas (Lama → Baru)','Asrama (Lama → Baru)','Catatan','Aksi'] as $h)
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
                            <td class="px-4 py-3 text-siakad-dark dark:text-white">
                                @if($r['nama_baru'])
                                <span class="{{ $r['nama_berbeda'] ? 'text-amber-600 dark:text-amber-400 font-medium' : '' }}">
                                    {{ $r['nama_lama'] }} → {{ $r['nama_baru'] }}
                                </span>
                                @else
                                <span class="text-siakad-secondary dark:text-gray-400">{{ $r['nama_lama'] }} (tidak diubah)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-siakad-dark dark:text-white">
                                @if($r['kelas_baru'])
                                {{ $r['kelas_lama'] }} → {{ $r['kelas_baru'] }}
                                @else
                                <span class="text-siakad-secondary dark:text-gray-400">tidak diubah</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-siakad-dark dark:text-white">
                                @if($r['asrama_baru'])
                                {{ $r['asrama_lama'] }} → {{ $r['asrama_baru'] }}
                                @else
                                <span class="text-siakad-secondary dark:text-gray-400">tidak diubah</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(!empty($r['errors']))
                                @foreach($r['errors'] as $err)
                                <p class="text-xs" style="color:#dc2626;">{{ $err }}</p>
                                @endforeach
                                @elseif($r['nama_berbeda'])
                                <p class="text-xs" style="color:#92400e;">Nama beda dari data lama, cek dulu</p>
                                @else
                                <span class="text-xs text-siakad-secondary dark:text-gray-400">Siap disimpan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <select name="action_{{ $index }}"
                                    class="px-2.5 py-1.5 text-xs rounded-lg border
                                          border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900
                                          text-siakad-dark dark:text-white focus:ring-2 outline-none">
                                    <option value="approve" {{ empty($r['errors']) ? 'selected' : '' }}>Setujui</option>
                                    <option value="skip" {{ !empty($r['errors']) ? 'selected' : '' }}>Lewati</option>
                                </select>
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
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.santri.import-bulk') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary dark:text-gray-400
                      hover:text-siakad-dark dark:hover:text-white transition-colors">
                Batal, upload ulang
            </a>
        </div>
    </form>

    <script>
        function setSemuaAksi(aksi) {
            document.querySelectorAll('select[name^="action_"]').forEach(function(select) {
                select.value = aksi;
            });
        }
    </script>
</x-app-layout>