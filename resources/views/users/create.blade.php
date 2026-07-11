{{-- ============================================================ --}}
{{-- resources/views/users/create.blade.php (sysadmin)            --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Sysadmin\UserController::create()                             --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Tambah User</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('sysadmin.users.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Tambah User Baru</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">Buat akun pengguna sistem beserta hak aksesnya</p>
        </div>
    </div>

    <form method="POST" action="{{ route('sysadmin.users.store') }}" class="max-w-xl">
        @csrf
        @include('users._form', ['user' => null, 'submitLabel' => 'Simpan'])
    </form>
</x-app-layout>
