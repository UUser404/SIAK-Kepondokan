<x-app-layout>
    <x-slot name="header">Tambah Kategori Mata Pelajaran</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.kategori-mata-pelajaran.store') }}">
            @csrf
            @php $kategoriMataPelajaran = null; $submitLabel = 'Simpan Kategori'; @endphp
            @include('kategori-mata-pelajaran._form')
        </form>
    </div>
</x-app-layout>