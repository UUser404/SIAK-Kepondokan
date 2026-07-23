{{-- ============================================================ --}}
{{-- resources/views/asrama/kamar-index.blade.php                 --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Kesantrian\KamarController::index()                           --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Daftar Kamar</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Daftar Kamar</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Semua kamar dari seluruh asrama beserta status hunian
            </p>
        </div>
        <a href="{{ route('kesantrian.asrama.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold
                  border border-gray-200 text-siakad-dark hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
            Kelola per Asrama
        </a>
    </div>

    {{-- Filter --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="asrama_id" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">Semua Asrama</option>
                @foreach($asramaList as $a)
                <option value="{{ $a->id }}" @selected(request('asrama_id')==$a->id)>{{ $a->nama }}</option>
                @endforeach
            </select>
            <select name="jenis" onchange="this.form.submit()"
                class="px-3 py-2.5 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">Semua Jenis</option>
                <option value="putra" @selected(request('jenis')==='putra' )>Putra</option>
                <option value="putri" @selected(request('jenis')==='putri' )>Putri</option>
            </select>
        </form>
    </div>

    <div class="card-saas overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-saas">
                <thead style="background-color: rgba(35,76,106,0.04);">
                    <tr>
                        @foreach(['Asrama','Nomor Kamar','Lantai','Kapasitas','Terisi','Sisa','Status','Penghuni'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @forelse($kamarList as $kamar)
                    @php
                    $terisi = $kamar->penempatanAktif->count();
                    $sisa = $kamar->kapasitas - $terisi;
                    @endphp
                    <tr>
                        <td class="px-5 py-3.5 text-siakad-dark font-medium">{{ $kamar->asrama->nama ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $kamar->nomor_kamar }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $kamar->lantai ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $kamar->kapasitas }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $terisi }}</td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $sisa }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $sisa <= 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $sisa <= 0 ? 'Penuh' : "{$sisa} tersedia" }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                            @forelse($kamar->penempatanAktif as $p)
                            {{ $p->santri->nama_lengkap ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                            @empty
                            <span class="text-gray-400">Kosong</span>
                            @endforelse
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-siakad-secondary">
                            Tidak ada data kamar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kamarList->hasPages())
        <div class="px-5 py-4" style="border-top: 1px solid var(--border-color);">
            {{ $kamarList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>