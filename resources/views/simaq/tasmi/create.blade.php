<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Form Imtihan Tasmi' (1 Juz)</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-lg shadow-sm border-t-4 border-green-500">
            
            <form action="{{ route('simaq.tasmi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="santri_id" value="{{ $santri_id ?? 1 }}">
                @if(isset($is_huffazh) && $is_huffazh)
                    <input type="hidden" name="is_huffazh" value="1">
                    <div class="mb-4 bg-purple-50 text-purple-700 p-2 rounded text-sm font-bold text-center">🏆 Ujian Tasmi' Program Khusus Jam'iyyatul Huffazh</div>
                @endif

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-bold text-gray-900">Juz yang Diujikan</label>
                    <select name="juz" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">-- Pilih Juz --</option>
                        @for($j=1; $j<=30; $j++)
                            <option value="{{ $j }}">Juz {{ $j }}</option>
                        @endfor
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-900">Kesalahan Kelancaran</label>
                        <input type="number" name="total_kelancaran" min="0" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-lg rounded-lg block w-full p-3 text-center">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-900">Kesalahan Tajwid</label>
                        <input type="number" name="total_tajwid" min="0" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-lg rounded-lg block w-full p-3 text-center">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-bold text-gray-900">Kesalahan Makhraj</label>
                        <input type="number" name="total_makhraj" min="0" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-lg rounded-lg block w-full p-3 text-center">
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg">
                    Simpan Nilai Tasmi'
                </button>
            </form>

        </div>
    </div>
</x-app-layout>