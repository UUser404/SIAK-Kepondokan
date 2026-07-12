{{-- ============================================================ --}}
{{-- resources/views/pendidik/index.blade.php - revised          --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Tenaga Pendidik</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Tenaga Pendidik</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Kelola data guru dan staf pengajar
            </p>
        </div>
        <a href="{{ route('admin.pendidik.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
              text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
            style="background-color: var(--siakad-primary);"
            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pendidik
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau NIP..."
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
                @foreach(['guru'=>'Guru','wakil_kurikulum'=>'Wakil Kurikulum','kesantrian'=>'Kesantrian','admin'=>'Admin'] as $val => $lbl)
                <option value="{{ $val }}" @selected(request('role')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white transition"
                style="background-color: var(--siakad-primary);">Cari</button>
        </form>
    </div>

    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['#','Nama','NIP','Role','Status Kepegawaian','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($pendidik as $i => $p)
                    <tr class="dark:hover:bg-gray-700/50">
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                            {{ $pendidik->firstItem() + $i }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center
                                        text-sm font-bold text-white flex-shrink-0"
                                    style="background-color: var(--siakad-secondary);">
                                    {{ strtoupper(substr($p->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-siakad-dark">{{ $p->user->name }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $p->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-xs text-siakad-secondary">
                            {{ $p->nip ?? '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     bg-blue-100 text-blue-700 capitalize">
                                {{ str_replace('_', ' ', $p->user->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $p->status_kepegawaian === 'tetap'
                               ? 'bg-green-100 text-green-700'
                               : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($p->status_kepegawaian ?? '-') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.pendidik.show', $p) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5
                                             12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542
                                             7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.pendidik.edit', $p) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada data tenaga pendidik
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendidik->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $pendidik->links() }}
        </div>
        @endif
    </div>
</x-app-layout>