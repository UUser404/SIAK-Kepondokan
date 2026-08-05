{{-- ============================================================ --}}
{{-- resources/views/nilai/kurikulum-index.blade.php             --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Penilaian</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">Penilaian</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Monitor progress input nilai seluruh kelas
            </p>
        </div>
        @if($ta)
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-medium border"
                style="background-color: rgba(35,76,106,0.08);
                     color: var(--siakad-primary);
                     border-color: rgba(35,76,106,0.2);">
                {{ $ta->nama_lengkap }}
            </span>
            <form method="POST" action="{{ route('kurikulum.nilai.finalize-all') }}"
                onsubmit="return confirm('Finalisasi nilai akhir untuk SEMUA kelas & SEMUA mata pelajaran sekaligus? Proses ini bisa memakan waktu beberapa saat kalau datanya banyak.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl text-white
                       transition-all hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Finalisasi Semua Kelas
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Stat ringkas --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        @foreach([
        ['Total Kelas', $kelasList->count()],
        ['Total Mapel', $mapelList->count()],
        ['Sudah Final', collect($progressMap)->where('persen', 100)->count()],
        ] as [$label, $value])
        <div class="card-saas p-5 text-center">
            <p class="text-2xl font-bold text-siakad-dark">{{ $value }}</p>
            <p class="text-xs text-siakad-secondary mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Grid kelas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @forelse($kelasList as $kelas)
        @php $prog = $progressMap[$kelas->id] ?? ['sudah'=>0,'total'=>0,'persen'=>0]; @endphp
        <div class="card-saas p-5 hover:border-siakad-primary/30
                transition">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-semibold text-siakad-dark">{{ $kelas->nama }}</p>
                    <p class="text-xs text-siakad-secondary mt-0.5">
                        {{ $kelas->tingkatan->nama }} ·
                        {{ $kelas->jumlah_santri }} santri
                    </p>
                    <p class="text-xs text-siakad-secondary">
                        Wali: {{ $kelas->waliKelas?->name ?? '-' }}
                    </p>
                </div>
                @if($prog['persen'] === 100)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                         bg-green-100 text-green-700 flex-shrink-0">
                    Lengkap
                </span>
                @endif
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-siakad-secondary">
                        Progress nilai akhir
                        @if(($prog['total_mapel'] ?? 0) > 0)
                        ({{ $prog['total_mapel'] }} mapel ditugaskan)
                        @endif
                    </span>
                    <span class="font-semibold text-siakad-dark">{{ $prog['persen'] }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all"
                        style="width: {{ $prog['persen'] }}%;
                            background-color: {{ $prog['persen'] === 100 ? '#16a34a' : 'var(--siakad-primary)' }}">
                    </div>
                </div>
                @if(($prog['total_mapel'] ?? 0) === 0)
                <p class="text-xs mt-1" style="color:#92400e;">Belum ada guru ditugaskan (Penugasan Mengajar) di kelas ini.</p>
                @endif
            </div>

            <a href="{{ route('kurikulum.nilai.show', ['kelas_id'=>$kelas->id]) }}"
                class="block w-full text-center py-2 text-xs font-semibold rounded-xl
                  border transition hover:-translate-y-0.5"
                style="border-color: var(--siakad-primary); color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='rgba(35,76,106,0.08)'"
                onmouseout="this.style.backgroundColor='transparent'">
                Lihat Detail Nilai
            </a>

            <form method="POST" action="{{ route('kurikulum.nilai.finalize-kelas') }}" class="mt-2"
                onsubmit="return confirm('Finalisasi nilai akhir {{ $kelas->nama }} untuk SEMUA mata pelajaran sekaligus?')">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <button type="submit"
                    class="block w-full text-center py-2 text-xs font-semibold rounded-xl text-white transition
                       hover:-translate-y-0.5"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Finalisasi Semua Mapel
                </button>
            </form>
        </div>
        @empty
        <div class="col-span-3 card-saas p-12 text-center">
            <p class="text-siakad-secondary text-sm">
                Belum ada kelas aktif
            </p>
        </div>
        @endforelse
    </div>
</x-app-layout>