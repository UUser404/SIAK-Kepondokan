<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Setoran Harian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-6 pb-4 border-b border-gray-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $santri->nama ?? $santri->nama_lengkap ?? 'Tanpa Nama' }}</h3>
                            <p class="text-sm text-gray-500 mt-1">NIS: <span class="font-mono">{{ $santri->nis ?? '-' }}</span></p>
                        </div>
                        <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded text-xs font-semibold border border-blue-200">
                            Sistem Kalkulasi Otomatis (Demerit)
                        </div>
                    </div>

                    <!-- FORM INPUT NILAI -->
                    <form action="{{ route('simaq.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="santri_id" value="{{ $santri->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Tanggal Setoran -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Tanggal Setoran</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            </div>

                            <!-- Program -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Program</label>
                                <select name="program" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                                    <option value="hafalan">Hafalan (Tahfizh)</option>
                                    <option value="tahsin">Tahsin (Perbaikan Bacaan)</option>
                                    <option value="tilawah">Tilawah</option>
                                </select>
                            </div>

                            <!-- Materi / Surat -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700">Materi Setoran (Surat & Ayat / Halaman)</label>
                                <input type="text" name="surah_ayat" placeholder="Cth: Al-Baqarah ayat 1-15 (Ziyadah)" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                            </div>

                            <!-- PENGINPUTAN JUMLAH KESALAHAN -->
                            <div class="md:col-span-2 mt-4">
                                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b pb-2">Evaluasi Kesalahan (Isi Angka)</h4>
                                <p class="text-xs text-gray-500 mt-1 mb-4">Masukkan jumlah kesalahan santri. Biarkan "0" jika bacaan sempurna. Nilai akhir, predikat, dan bintang akan dihitung otomatis oleh sistem.</p>
                            </div>

                            <!-- Kesalahan Kelancaran -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Kesalahan Kelancaran</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="kesalahan_kelancaran" min="0" value="0" required class="flex-1 block w-full rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-xs">Kali Lupa/Tersendat</span>
                                </div>
                            </div>

                            <!-- Kesalahan Tajwid -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Kesalahan Tajwid</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="kesalahan_tajwid" min="0" value="0" required class="flex-1 block w-full rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-xs">Ghunnah/Mad/dll</span>
                                </div>
                            </div>

                            <!-- Kesalahan Makhraj -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Kesalahan Makhraj</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="kesalahan_makhraj" min="0" value="0" required class="flex-1 block w-full rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-xs">Salah Pengucapan</span>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div class="md:col-span-2 mt-2">
                                <label class="block text-sm font-semibold text-gray-700">Catatan Khusus (Opsional)</label>
                                <textarea name="catatan" rows="2" placeholder="Cth: Perbanyak muraja'ah di akhir ayat..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm"></textarea>
                            </div>

                        </div>

                        <!-- Tombol Submit -->
                        <div class="mt-8 flex justify-end gap-3">
                            <a href="{{ route('simaq.detail', $santri->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-md transition-all">
                                Hitung & Simpan Nilai
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>