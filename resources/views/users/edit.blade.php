{{-- ============================================================ --}}
{{-- resources/views/users/edit.blade.php (sysadmin)              --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Sysadmin\UserController::edit()                                --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit User</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('sysadmin.users.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Edit User — {{ $user->name }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">Perbarui data akun & hak akses pengguna</p>
        </div>
    </div>

    <form method="POST" action="{{ route('sysadmin.users.update', $user) }}" class="max-w-xl">
        @csrf
        @method('PUT')
        @include('users._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-app-layout>