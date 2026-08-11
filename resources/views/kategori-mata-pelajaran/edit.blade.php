<x-app-layout>
    <x-slot name="header">Edit Kategori Mata Pelajaran</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.kategori-mata-pelajaran.update', $kategoriMataPelajaran) }}">
            @csrf
            @method('PUT')
            @php $submitLabel = 'Simpan Perubahan'; @endphp
            @include('kategori-mata-pelajaran._form')
        </form>
    </div>
</x-app-layout>