{{-- ============================================================ --}}
{{-- resources/views/nilai/guru-index.blade.php                  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Input Nilai</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-siakad-dark">Input Nilai</h2>
        <p class="text-sm text-siakad-secondary mt-0.5">
            Pilih kelas dan mata pelajaran untuk input nilai
        </p>
    </div>

    {{-- Tahun ajaran info --}}
    @if($ta)
    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-siakad-primary to-siakad-dark">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2
                         2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-white/70 text-xs">Tahun Ajaran Aktif</p>
                <p class="text-white font-semibold">{{ $ta->nama_lengkap }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Daftar kelas-mapel --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($jadwalList as $jadwal)
        <div class="card-saas p-5 hover:border-siakad-primary/30
                transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-siakad-dark truncate">
                        {{ $jadwal->mataPelajaran->nama }}
                    </p>
                    <p class="text-sm text-siakad-secondary mt-0.5">
                        {{ $jadwal->kelas->nama }} · {{ $jadwal->kelas->tingkatan->nama }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ml-3"
                    style="background-color: rgba(35,76,106,0.1);">
                    <svg class="w-5 h-5" style="color: var(--siakad-primary);"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
            </div>

            {{-- Progress input --}}
            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-siakad-secondary">Progress input nilai</span>
                    <span class="font-semibold text-siakad-dark">
                        {{ $jadwal->persen }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all"
                        style="width: {{ $jadwal->persen }}%;
                            background-color: {{ $jadwal->persen === 100
                                ? '#16a34a' : 'var(--siakad-primary)' }}">
                    </div>
                </div>
                <p class="text-xs text-siakad-secondary mt-1">
                    {{ $jadwal->sudah_input }}/{{ $jadwal->total_santri }} santri
                </p>
            </div>

            <a href="{{ route('guru.nilai.show', [$jadwal->kelas_id, $jadwal->mata_pelajaran_id]) }}"
                class="block w-full text-center py-2 text-sm font-semibold rounded-xl text-white
                  transition hover:-translate-y-0.5 hover:shadow-md"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $jadwal->persen === 100 ? 'Lihat / Edit Nilai' : 'Input Nilai' }}
            </a>
        </div>
        @empty
        <div class="col-span-3 card-saas p-12 text-center">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                style="background-color: rgba(35,76,106,0.08);">
                <svg class="w-8 h-8 text-siakad-secondary"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                         m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <p class="text-siakad-secondary text-sm">
                Belum ada jadwal mengajar yang terdaftar
            </p>
        </div>
        @endforelse
    </div>
</x-app-layout>