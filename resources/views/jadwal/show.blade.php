<x-app-layout>
    <x-slot name="header">Detail Jadwal</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kurikulum.jadwal.index') }}"
            class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">
            {{ $jadwal->mataPelajaran->nama }}
        </h2>
    </div>

    <div class="max-w-xl card-saas dark:bg-gray-800 p-5">
        <dl class="space-y-3 text-sm">
            @foreach([
            ['Kelas', $jadwal->kelas->nama],
            ['Mata Pelajaran', $jadwal->mataPelajaran->nama],
            ['Guru', $jadwal->guru->nama], {{-- Diubah menjadi ->nama dari model TenagaPendidik --}}
            ['Hari', $jadwal->hari],
            ['Jam', \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i').' - '.\Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i')],
            ['Ruangan', $jadwal->ruangan ?? '—'],
            ] as [$label, $value])
            <div class="flex gap-3">
                <dt class="w-32 flex-shrink-0 text-siakad-secondary dark:text-gray-400">{{ $label }}</dt>
                <dd class="font-medium text-siakad-dark dark:text-white">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</x-app-layout>