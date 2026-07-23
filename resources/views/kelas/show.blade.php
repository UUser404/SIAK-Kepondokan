<x-app-layout>
    <x-slot name="header">{{ $kelas->nama }}</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('kurikulum.kelas.index') }}"
                class="p-2 rounded-xl border border-gray-200 dark:border-gray-700
                  text-siakad-secondary dark:text-gray-400
                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold text-siakad-dark dark:text-white">{{ $kelas->nama }}</h2>
                <p class="text-sm text-siakad-secondary dark:text-gray-400">
                    {{ $kelas->tingkatan->nama }} · Wali: {{ $kelas->waliKelas?->name ?? '-' }}
                </p>
            </div>
        </div>
        <a href="{{ route('kurikulum.kelas.edit', $kelas) }}"
            class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700
              text-siakad-secondary dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            Edit Kelas
        </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        {{-- Daftar santri --}}
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">
                    Santri ({{ $santriList->count() }})
                </h3>
            </div>
            <div class="divide-y dark:divide-gray-700 max-h-96 overflow-y-auto" style="border-color: var(--border-color);">
                @forelse($santriList as $i => $s)
                <div class="px-5 py-2.5 flex items-center gap-3">
                    <span class="text-xs text-siakad-secondary dark:text-gray-500 w-5">{{ $i+1 }}</span>
                    <p class="text-sm text-siakad-dark dark:text-white flex-1">{{ $s->nama_lengkap }}</p>
                    <span class="text-xs text-siakad-secondary dark:text-gray-400">{{ $s->nis }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-siakad-secondary dark:text-gray-400">
                    Belum ada santri di kelas ini
                </div>
                @endforelse
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark dark:text-white">Jadwal Pelajaran</h3>
            </div>
            <div class="divide-y dark:divide-gray-700 max-h-96 overflow-y-auto" style="border-color: var(--border-color);">
                @forelse($jadwalList as $j)
                <div class="px-5 py-2.5">
                    <p class="text-sm font-medium text-siakad-dark dark:text-white">{{ $j->mataPelajaran->nama }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">
                        {{ ucfirst($j->hari) }} ·
                        {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                        · {{ $j->guru->name }} {{-- $j->guru() -> belongsTo(User::class), field-nya 'name' --}}
                    </p>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-siakad-secondary dark:text-gray-400">
                    Belum ada jadwal
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>