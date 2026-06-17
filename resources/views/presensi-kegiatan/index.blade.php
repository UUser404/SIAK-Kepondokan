{{-- ============================================================ --}}
{{-- resources/views/presensi-kegiatan/index.blade.php - revised --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Presensi Kegiatan Harian</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                Presensi Kegiatan Harian
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                Pantau kehadiran santri di setiap kegiatan pondok
            </p>
        </div>
        <a href="{{ route('kesantrian.rekap.presensi') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border
              border-gray-200 text-siakad-secondary
              hover:bg-gray-50 transition">
            Rekap Bulanan →
        </a>
    </div>

    {{-- Filter tanggal --}}
    <div class="card-saas p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <label class="text-sm font-medium text-siakad-dark">Tanggal:</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                max="{{ today()->format('Y-m-d') }}"
                onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200
                      bg-gray-50 text-siakad-dark
                      focus:ring-2 outline-none transition">
            <span class="text-sm text-siakad-secondary">
                {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </span>
        </form>
    </div>

    {{-- Grid kegiatan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($ringkasanHariIni as $item)
        @php $k = $item['kegiatan']; @endphp
        <div class="card-saas p-5 hover:border-siakad-primary/30
                transition">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs text-siakad-secondary">
                        {{ \Carbon\Carbon::parse($k->waktu_default)->format('H:i') }} WIB
                    </p>
                    <p class="font-semibold text-siakad-dark mt-0.5 text-sm leading-snug">
                        {{ $k->nama }}
                    </p>
                </div>
                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1
                        {{ $item['sudah_input'] > 0 ? 'bg-green-400' : 'bg-gray-300' }}">
                </div>
            </div>

            {{-- Progress --}}
            <div class="mb-3">
                <div class="w-full bg-gray-100 rounded-full h-2 mb-1.5">
                    <div class="h-2 rounded-full transition-all duration-500"
                        style="width: {{ $item['persen'] }}%;
                            background-color: {{ $item['persen'] >= 80 ? '#16a34a' : ($item['persen'] >= 50 ? 'var(--siakad-primary)' : '#dc2626') }}">
                    </div>
                </div>
                <div class="flex justify-between text-xs text-siakad-secondary">
                    <span>{{ $item['sudah_input'] }}/{{ $item['total'] }} santri</span>
                    <span class="font-semibold">{{ $item['persen'] }}%</span>
                </div>
            </div>

            <a href="{{ route('kesantrian.presensi.show', [$tanggal, $k->id]) }}"
                class="block w-full text-center py-2 text-xs font-semibold rounded-xl transition-all
                  hover:-translate-y-0.5
                  {{ $item['sudah_input'] > 0
                     ? 'border border-siakad-primary/30 text-siakad-primary hover:bg-siakad-primary/5'
                     : 'text-white hover:shadow-md' }}"
                @if($item['sudah_input']===0)
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'"
                @endif>
                {{ $item['sudah_input'] > 0 ? 'Lihat / Edit' : 'Input Presensi' }}
            </a>
        </div>
        @endforeach
    </div>
</x-app-layout>