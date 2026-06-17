{{-- ============================================================ --}}
{{-- resources/views/surat/index.blade.php                       --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Surat Keluar</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Surat Keluar</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Kelola surat keluar dan dokumen resmi pondok
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.template-surat.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium
                  border border-gray-200
                  text-siakad-secondary
                  hover:bg-gray-50 transition">
                Template
            </a>
            <a href="{{ route('admin.surat.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                  text-white transition-all hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Surat
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
        ['Total Surat', $stats['total'], 'blue'],
        ['Draft', $stats['draft'], 'yellow'],
        ['Diterbitkan', $stats['terbit'], 'green'],
        ['Bulan Ini', $stats['bulan_ini'],''],
        ] as [$label, $value, $color])
        <div class="card-saas p-5">
            <p class="text-2xl font-bold {{ $color ? "text-{$color}-600" : 'text-siakad-dark' }}">
                {{ $value }}
            </p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor / perihal..."
                    class="block w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border
                          border-gray-200 bg-gray-50
                          text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            </div>
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status')==='draft' )>Draft</option>
                <option value="diterbitkan" @selected(request('status')==='diterbitkan' )>Diterbitkan</option>
            </select>
            <select name="bulan" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition">
                <option value="">Semua Bulan</option>
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" @selected(request('bulan')==$b)>
                    {{ \Carbon\Carbon::create()->month($b)->locale('id')->isoFormat('MMMM') }}
                </option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2.5 text-sm font-medium rounded-xl text-white"
                style="background-color: var(--siakad-primary);">Cari</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Nomor Surat','Perihal','Kepada','Santri','Tanggal','Status','Aksi'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                               text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($suratList as $s)
                    <tr class="dark:hover:bg-gray-700/30">
                        <td class="px-5 py-3.5 font-mono text-xs font-semibold whitespace-nowrap"
                            style="color: var(--siakad-primary);">
                            {{ $s->nomor_surat }}
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-siakad-dark max-w-[200px] truncate">
                                {{ $s->perihal }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary
                               max-w-[140px] truncate">
                            {{ $s->ditujukan_kepada ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary">
                            {{ $s->santri?->nama_lengkap ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-siakad-secondary whitespace-nowrap">
                            {{ $s->tanggal_surat->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $s->status === 'draft'
                               ? 'bg-yellow-100 text-yellow-700'
                               : 'bg-green-100 text-green-700' }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.surat.show', $s) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523
                                             5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064
                                             7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @if($s->status === 'draft')
                                <a href="{{ route('admin.surat.edit', $s) }}"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-siakad-primary/10 hover:text-siakad-primary transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endif
                                <a href="{{ route('admin.surat.cetak', $s) }}" target="_blank"
                                    class="p-2 rounded-lg text-siakad-secondary
                                      hover:bg-blue-50 hover:text-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0
                                             002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2
                                             2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-sm
                                           text-siakad-secondary">
                            Belum ada surat keluar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratList->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $suratList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>