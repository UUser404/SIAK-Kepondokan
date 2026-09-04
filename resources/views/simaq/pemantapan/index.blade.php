<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ujian Pemantapan SIMAQ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-700">Daftar Santri Siap Ujian</h3>
                        <!-- Contoh tombol statis untuk testing -->
                        <a href="{{ route('simaq.pemantapan.create', ['santri_id' => 1]) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            + Uji Santri (Testing)
                        </a>
                    </div>
                    <p class="text-sm text-gray-500">Pilih santri untuk memulai ujian pemantapan dengan 10 pertanyaan sambung ayat.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>