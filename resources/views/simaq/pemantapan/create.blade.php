<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Ujian Pemantapan (10 Soal)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('simaq.pemantapan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="santri_id" value="{{ $santri_id }}">

                    <div class="mb-6">
                        <p class="text-sm text-gray-600 font-medium bg-orange-50 p-3 rounded-lg border border-orange-100">
                            <strong>Instruksi:</strong> Masukkan jumlah kesalahan pada masing-masing kriteria untuk 10 pertanyaan sambung ayat. Jika lancar sempurna, biarkan angka 0.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-center w-16">Soal</th>
                                    <th scope="col" class="px-4 py-3 text-center">Kesalahan Kelancaran</th>
                                    <th scope="col" class="px-4 py-3 text-center">Kesalahan Tajwid</th>
                                    <th scope="col" class="px-4 py-3 text-center">Kesalahan Makhraj</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 10; $i++)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-bold text-center text-gray-900">{{ $i }}</td>
                                    <td class="px-4 py-2"><input type="number" name="soal[{{$i}}][kelancaran]" min="0" value="0" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg text-center p-2"></td>
                                    <td class="px-4 py-2"><input type="number" name="soal[{{$i}}][tajwid]" min="0" value="0" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg text-center p-2"></td>
                                    <td class="px-4 py-2"><input type="number" name="soal[{{$i}}][makhraj]" min="0" value="0" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg text-center p-2"></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg shadow">
                            Simpan & Hitung Nilai Akhir
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>