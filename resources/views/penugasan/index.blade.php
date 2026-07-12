{{-- ============================================================ --}}
{{-- resources/views/penugasan/index.blade.php                    --}}
{{-- Langkah 1: pilih guru yang mau diatur penugasan mengajarnya  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Penugasan Mengajar</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark">Penugasan Mengajar</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Pilih guru, lalu tentukan mata pelajaran dan kelas yang diampunya
            @if($ta) &middot; Tahun Ajaran {{ $ta->nama }} ({{ ucfirst($ta->semester) }}) @endif
        </p>
    </div>

    @if(!$ta)
    <div class="p-4 mb-6 rounded-xl text-sm" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e;">
        Belum ada tahun ajaran aktif. Aktifkan salah satu tahun ajaran terlebih dahulu di Data Master.
    </div>
    @endif

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
                    placeholder="Cari nama guru..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-semibold rounded-xl text-white transition-all"
                style="background-color: var(--siakad-primary);">
                Cari
            </button>
        </form>
    </div>

    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Nama Guru','Email','Jumlah Penugasan',''] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($guruList as $guru)
                    <tr>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold
                                    text-white flex-shrink-0" style="background-color: var(--siakad-primary);">
                                    {{ strtoupper(substr($guru->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-siakad-dark">{{ $guru->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $guru->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $guru->jumlah_penugasan > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $guru->jumlah_penugasan }} penugasan
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('kurikulum.penugasan.show', $guru) }}"
                                class="text-xs font-semibold transition-colors" style="color: var(--siakad-primary);">
                                Atur Penugasan →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-siakad-secondary">
                            Tidak ada data guru
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($guruList->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $guruList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
