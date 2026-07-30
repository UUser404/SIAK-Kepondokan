<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Santri Halaqah') }} - SIMAQ
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Pilih Santri untuk Evaluasi Hafalan</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3">No</th>
                                    <th class="px-6 py-3">NIS</th>
                                    <th class="px-6 py-3">Nama Lengkap Santri</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($santris as $index => $santri)
                                    <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-mono text-gray-600">{{ $santri->nis ?? '-' }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ $santri->nama ?? $santri->nama_lengkap ?? 'Tanpa Nama' }}</td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('simaq.detail', $santri->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold uppercase tracking-wider hover:bg-blue-700 transition-colors shadow-sm">
                                                Input & Riwayat &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">
                                            Belum ada data santri di dalam database SIAK.
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