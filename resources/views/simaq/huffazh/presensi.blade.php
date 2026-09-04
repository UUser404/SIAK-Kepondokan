<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Presensi Jam'iyyatul Huffazh</h2>
    </x-slot>

    <div class="py-12 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            
            <form action="{{ route('simaq.huffazh.presensi.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-bold text-gray-900">Tanggal Pertemuan</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="w-48 bg-gray-50 border border-gray-300 text-gray-900 rounded-lg p-2">
                </div>

                <table class="w-full text-sm text-left text-gray-500 border mt-4">
                    <thead class="bg-gray-100 border-b text-gray-700">
                        <tr>
                            <th class="px-4 py-3">Nama Santri (Member Huffazh)</th>
                            <th class="px-4 py-3 text-center">Hadir</th>
                            <th class="px-4 py-3 text-center">Sakit</th>
                            <th class="px-4 py-3 text-center">Izin</th>
                            <th class="px-4 py-3 text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Contoh statis 1 santri -->
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium text-gray-900">Fulan bin Fulan</td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="kehadiran[1]" value="H" checked class="text-green-600 w-5 h-5"></td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="kehadiran[1]" value="S" class="text-yellow-500 w-5 h-5"></td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="kehadiran[1]" value="I" class="text-blue-500 w-5 h-5"></td>
                            <td class="px-4 py-3 text-center"><input type="radio" name="kehadiran[1]" value="A" class="text-red-600 w-5 h-5"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
                        Simpan Presensi
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>