{{-- ============================================================ --}}
{{-- resources/views/users/index.blade.php (sysadmin)           --}}
{{-- ============================================================ --}}
<x-app-layout>
<x-slot name="header">Manajemen User</x-slot>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-siakad-dark">Manajemen User</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Kelola akun dan hak akses pengguna sistem
        </p>
    </div>
    <a href="{{ route('sysadmin.users.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
       style="background-color: var(--siakad-primary);"
       onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
       onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah User
    </a>
</div>

{{-- Filter --}}
<div class="card-saas p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama / email..."
                   class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>
        <select name="role" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
            <option value="">Semua Role</option>
            @foreach(['mudir','wakil_kurikulum','guru','kesantrian','admin','sysadmin'] as $r)
            <option value="{{ $r }}" @selected(request('role') === $r)>
                {{ ucfirst(str_replace('_',' ',$r)) }}
            </option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
            <option value="">Semua Status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>
        <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cari</button>
    </form>
</div>

<div class="card-saas overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-saas">
            <thead style="background-color: rgba(35,76,106,0.04);">
                <tr>
                    @foreach(['#','Nama','Email','Role','Status','Login Terakhir','Aksi'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border-color);">
                @forelse($users as $i => $user)
                <tr class="dark:hover:bg-gray-700/30">
                    <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                        {{ $users->firstItem() + $i }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center
                                        text-xs font-bold text-white flex-shrink-0"
                                 style="background-color: {{ $user->is_active ? 'var(--siakad-primary)' : '#9CA3AF' }};">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <p class="font-medium text-siakad-dark">{{ $user->name }}</p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                        {{ $user->email }}
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     bg-blue-100 text-blue-700 capitalize">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1 text-xs font-medium
                                     {{ $user->is_active
                                        ? 'text-green-600'
                                        : 'text-gray-400' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('sysadmin.users.edit', $user) }}"
                               class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('sysadmin.users.toggle', $user) }}">
                                @csrf @method('POST')
                                <button type="submit"
                                        class="p-2 rounded-lg transition text-siakad-secondary
                                               {{ $user->is_active
                                                  ? 'hover:bg-red-50 hover:text-red-600'
                                                  : 'hover:bg-green-50 hover:text-green-600' }}"
                                        title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($user->is_active)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('sysadmin.users.reset-password', $user) }}"
                                  onsubmit="return confirm('Reset password {{ addslashes($user->name) }} ke default?')">
                                @csrf
                                <button type="submit"
                                        class="p-2 rounded-lg text-siakad-secondary
                                               hover:bg-yellow-50 hover:text-yellow-600 transition"
                                        title="Reset Password">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1
                                                 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                        Tidak ada user ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
        {{ $users->links() }}
    </div>
    @endif
</div>
</x-app-layout>