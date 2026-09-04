<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jam'iyyatul Huffazh</h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Kotak Absensi -->
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
            <h3 class="text-lg font-bold mb-2">Presensi Huffazh (2x Sepekan)</h3>
            <p class="text-sm text-gray-500 mb-4">Catat kehadiran santri program intensif.</p>
            <a href="{{ route('simaq.huffazh.presensi.index') }}" class="inline-block bg-blue-100 text-blue-700 font-medium px-4 py-2 rounded-lg hover:bg-blue-200">
                Buka Absensi &rarr;
            </a>
        </div>

        <!-- Kotak Tasmi Khusus -->
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
            <h3 class="text-lg font-bold mb-2">Imtihan Tasmi' Huffazh</h3>
            <p class="text-sm text-gray-500 mb-4">Ujian 1 Juz khusus anggota program intensif.</p>
            <a href="{{ route('simaq.huffazh.tasmi.create', ['santri_id' => 1]) }}" class="inline-block bg-purple-100 text-purple-700 font-medium px-4 py-2 rounded-lg hover:bg-purple-200">
                Input Tasmi' Huffazh &rarr;
            </a>
        </div>

    </div>
</x-app-layout>