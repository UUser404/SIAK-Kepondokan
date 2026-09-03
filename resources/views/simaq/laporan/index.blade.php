<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cetak Rapor SIMAQ Individu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg font-bold border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-4">Pilih Santri untuk Dicetak Rapornya</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 border">No</th>
                                    <th class="px-6 py-3 border">NIS</th>
                                    <th class="px-6 py-3 border">Nama Lengkap Santri</th>
                                    <th class="px-6 py-3 border text-center">Aksi Cetak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($santris as $index => $santri)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 border">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 border font-mono">{{ $santri->nis ?? '-' }}</td>
                                        <td class="px-6 py-4 border font-bold text-gray-900">{{ $santri->nama ?? $santri->nama_lengkap }}</td>
                                        <td class="px-6 py-4 border text-center">
                                            <a href="{{ route('simaq.laporan.cetak', $santri->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-bold uppercase hover:bg-indigo-700 transition-colors shadow-sm">
                                                🖨️ Cetak Rapor
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>