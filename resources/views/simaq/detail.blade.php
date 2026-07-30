<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Riwayat Hafalan Santri') }}
            </h2>
            <a href="{{ route('simaq.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali ke Daftar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Profil Singkat Santri & Tombol Tambah -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 flex justify-between items-center p-6 border-l-4 border-blue-500">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $santri->nama ?? $santri->nama_lengkap ?? 'Tanpa Nama' }}</h3>
                    <p class="text-sm text-gray-500 mt-1">NIS: <span class="font-mono text-gray-700">{{ $santri->nis ?? '-' }}</span></p>
                </div>
                <div>
                    <a href="{{ route('simaq.create', $santri->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-md transition-all">
                        + Tambah Setoran Baru
                    </a>
                </div>
            </div>

            <!-- Tabel Riwayat Penilaian -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">Riwayat Setoran Terakhir</h4>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Jenis</th>
                                    <th class="px-4 py-3">Surat / Ayat</th>
                                    <th class="px-4 py-3 text-center">Kelancaran</th>
                                    <th class="px-4 py-3 text-center">Tajwid</th>
                                    <th class="px-4 py-3">Catatan Ustadz</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penilaians as $nilai)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ date('d M Y', strtotime($nilai->tanggal)) }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-700">{{ $nilai->jenis_penilaian }}</td>
                                        <td class="px-4 py-3">{{ $nilai->materi }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-blue-600">{{ $nilai->nilai_kelancaran }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-green-600">{{ $nilai->nilai_tajwid }}</td>
                                        <td class="px-4 py-3 text-xs italic">{{ $nilai->catatan ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <!-- Tombol Hapus (Opsional) -->
                                            <form action="{{ route('simaq.destroy', $nilai->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus nilai ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                                            Santri ini belum memiliki riwayat setoran. Silakan tambah nilai baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>