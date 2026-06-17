{{-- ============================================================ --}}
{{-- resources/views/asrama/show.blade.php                       --}}
{{-- Detail asrama + daftar kamar + tambah kamar                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">{{ $asrama->nama }}</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kesantrian.asrama.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $asrama->nama }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Asrama {{ ucfirst($asrama->jenis) }}
                @if($asrama->pengurus) · Pengurus: {{ $asrama->pengurus }} @endif
            </p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Daftar Kamar --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($asrama->kamar as $kamar)
            @php
            $penghuni = $kamar->penghuni;
            $sisa = $kamar->kapasitas - $kamar->penghuni;
            @endphp
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center justify-between"
                    style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                                 text-white" style="background-color: var(--siakad-primary);">
                            {{ $kamar->nomor_kamar }}
                        </span>
                        <div>
                            <p class="font-semibold text-sm text-siakad-dark">
                                Kamar {{ $kamar->nomor_kamar }}
                            </p>
                            <p class="text-xs text-siakad-secondary">
                                Lantai {{ $kamar->lantai ?? '-' }} ·
                                {{ $kamar->penghuni }}/{{ $kamar->kapasitas }} penghuni
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $sisa === 0
                       ? 'bg-red-100 text-red-700'
                       : 'bg-green-100 text-green-700' }}">
                        {{ $sisa === 0 ? 'Penuh' : "{$sisa} tersedia" }}
                    </span>
                </div>

                {{-- Penghuni --}}
                @if($kamar->penempatanAktif->count() > 0)
                <div class="divide-y" style="border-color: var(--border-color);">
                    @foreach($kamar->penempatanAktif as $p)
                    <div class="px-5 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                                    text-white" style="background-color: var(--siakad-secondary);">
                                {{ strtoupper(substr($p->santri->nama_lengkap, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-siakad-dark">
                                    {{ $p->santri->nama_lengkap }}
                                </p>
                                <p class="text-xs text-siakad-secondary">
                                    {{ $p->santri->nis }} · Masuk: {{ $p->tanggal_masuk->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <form method="POST"
                            action="{{ route('kesantrian.kamar.keluarkan', $p) }}"
                            onsubmit="return confirm('Keluarkan santri dari kamar ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="tanggal_keluar" value="{{ today()->format('Y-m-d') }}">
                            <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 transition">
                                Keluarkan
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-4 text-xs text-siakad-secondary">
                    Belum ada penghuni
                </div>
                @endif
            </div>
            @empty
            <div class="card-saas p-12 text-center">
                <p class="text-siakad-secondary text-sm">Belum ada kamar</p>
            </div>
            @endforelse
        </div>

        {{-- Sidebar: tambah kamar + tempatkan santri --}}
        <div class="space-y-5">
            {{-- Form tambah kamar --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Tambah Kamar</h3>
                </div>
                <div class="p-5">
                    <form method="POST" action="{{ route('kesantrian.kamar.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="asrama_id" value="{{ $asrama->id }}">
                        <div>
                            <label class="block text-xs text-siakad-secondary mb-1">
                                Nomor Kamar
                            </label>
                            <input type="text" name="nomor_kamar" placeholder="e.g. 101"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                                      bg-gray-50
                                      text-siakad-dark focus:ring-2 outline-none"
                                required>
                        </div>
                        <div>
                            <label class="block text-xs text-siakad-secondary mb-1">
                                Kapasitas
                            </label>
                            <input type="number" name="kapasitas" value="4" min="1" max="20"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                                      bg-gray-50
                                      text-siakad-dark focus:ring-2 outline-none"
                                required>
                        </div>
                        <div>
                            <label class="block text-xs text-siakad-secondary mb-1">
                                Lantai
                            </label>
                            <input type="text" name="lantai" placeholder="e.g. 1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200
                                      bg-gray-50
                                      text-siakad-dark focus:ring-2 outline-none">
                        </div>
                        <button type="submit"
                            class="w-full py-2 text-sm font-semibold rounded-xl text-white transition"
                            style="background-color: var(--siakad-primary);"
                            onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                            onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                            Tambah Kamar
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>