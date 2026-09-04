<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Imtihan Tasmi' SIMAQ</h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Jadwal Tasmi' Santri</h3>
                <a href="{{ route('simaq.tasmi.create', ['santri_id' => 1]) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    + Input Tasmi' 1 Juz (Testing)
                </a>
            </div>
            <p class="text-sm text-gray-500">Ujian setoran full 1 Juz sekali duduk.</p>
        </div>
    </div>
</x-app-layout>