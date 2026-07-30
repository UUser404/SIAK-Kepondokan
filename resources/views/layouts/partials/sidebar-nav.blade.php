{{-- resources/views/layouts/partials/sidebar-nav.blade.php --}}
@php
$role = Auth::user()->role;
$current = request()->route()?->getName() ?? '';

$active = fn(string $prefix) => str_starts_with($current, $prefix)
? 'active'
: '';
@endphp

{{-- ==================== MUDIR ==================== --}}
@if($role === 'mudir')

<a href="{{ route('mudir.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('mudir.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Laporan</p>
</div>
<a href="{{ route('mudir.laporan.akademik') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('mudir.laporan.akademik') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Akademik</span>
</a>
<a href="{{ route('mudir.laporan.kesantrian') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('mudir.laporan.kesantrian') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    <span class="sidebar-text">Kesantrian</span>
</a>
<a href="{{ route('mudir.laporan.presensi') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('mudir.laporan.presensi') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
    </svg>
    <span class="sidebar-text">Presensi</span>
</a>

@endif

{{-- ==================== WAKIL KURIKULUM ==================== --}}
@if($role === 'wakil_kurikulum')

<a href="{{ route('kurikulum.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('kurikulum.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Akademik</p>
</div>
@foreach([
['route'=>'kurikulum.kelas.index', 'prefix'=>'kurikulum.kelas', 'label'=>'Kelas', 'icon'=>'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
// Menu 'Jadwal' sengaja disembunyikan dulu (fitur jadwal hari/jam tidak dipakai untuk saat ini).
// Digantikan oleh menu 'Penugasan' di bawah, yang menentukan mapel & kelas yang diampu guru
// tanpa perlu jadwal hari/jam.
['route'=>'kurikulum.penugasan.index', 'prefix'=>'kurikulum.penugasan', 'label'=>'Penugasan', 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
['route'=>'kurikulum.nilai.index', 'prefix'=>'kurikulum.nilai', 'label'=>'Penilaian', 'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
['route'=>'kurikulum.rapor.index', 'prefix'=>'kurikulum.rapor', 'label'=>'Rapor', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">AI Tools</p>
</div>
<a href="{{ route('kurikulum.ai.index') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
              {{ $active('kurikulum.ai') }} bg-gradient-to-r from-purple-500/10 to-indigo-500/10
              hover:from-purple-500/20 hover:to-indigo-500/20"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
    <span class="sidebar-text">AI Advisor</span>
</a>

@endif

{{-- ==================== GURU ==================== --}}
@if($role === 'guru')

<a href="{{ route('guru.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('guru.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Mengajar</p>
</div>
@foreach([
['route'=>'guru.presensi.index', 'prefix'=>'guru.presensi', 'label'=>'Presensi KBM', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
['route'=>'guru.nilai.index', 'prefix'=>'guru.nilai', 'label'=>'Input Nilai', 'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
['route'=>'guru.jurnal.index', 'prefix'=>'guru.jurnal', 'label'=>'Jurnal Mengajar','icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">AI Tools</p>
</div>
<a href="{{ route('guru.ai.index') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
              {{ $active('guru.ai') }} bg-gradient-to-r from-purple-500/10 to-indigo-500/10"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
    <span class="sidebar-text">AI Advisor</span>
</a>


@if(Auth::user()->isWaliKelas())
<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Wali Kelas</p>
</div>
<a href="{{ route('wali-kelas.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('wali-kelas') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    <span class="sidebar-text">Dashboard Wali Kelas</span>
</a>
@endif
@endif

{{-- ==================== KESANTRIAN ==================== --}}
@if($role === 'kesantrian')

<a href="{{ route('kesantrian.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('kesantrian.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Kegiatan</p>
</div>
<a href="{{ route('kesantrian.presensi.index') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('kesantrian.presensi') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
    </svg>
    <span class="sidebar-text">Presensi Harian</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Asrama</p>
</div>
@foreach([
['route'=>'kesantrian.asrama.index', 'prefix'=>'kesantrian.asrama', 'label'=>'Asrama', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
['route'=>'kesantrian.kamar.index', 'prefix'=>'kesantrian.kamar', 'label'=>'Kamar', 'icon'=>'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
['route'=>'kesantrian.pelanggaran.index', 'prefix'=>'kesantrian.pelanggaran', 'label'=>'Pelanggaran', 'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
['route'=>'kesantrian.prestasi.index', 'prefix'=>'kesantrian.prestasi', 'label'=>'Prestasi', 'icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

@endif

{{-- ==================== ADMIN ==================== --}}
@if($role === 'admin')

<a href="{{ route('admin.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('admin.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Data</p>
</div>
@foreach([
['route'=>'admin.santri.index', 'prefix'=>'admin.santri', 'label'=>'Santri', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
['route'=>'admin.pendidik.index', 'prefix'=>'admin.pendidik', 'label'=>'Tenaga Pendidik', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Master</p>
</div>
@foreach([
['route'=>'admin.tahun-ajaran.index', 'prefix'=>'admin.tahun-ajaran', 'label'=>'Tahun Ajaran', 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
['route'=>'admin.tingkatan.index', 'prefix'=>'admin.tingkatan', 'label'=>'Tingkatan', 'icon'=>'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
['route'=>'admin.mata-pelajaran.index', 'prefix'=>'admin.mata-pelajaran', 'label'=>'Mata Pelajaran', 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
['route'=>'admin.ekstrakurikuler.index', 'prefix'=>'admin.ekstrakurikuler', 'label'=>'Ekstrakurikuler', 'icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Layanan</p>
</div>
@foreach([
['route'=>'admin.ppdb.index', 'prefix'=>'admin.ppdb', 'label'=>'PPDB Online', 'icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21a12.318 12.318 0 01-6.374-1.766z'],
['route'=>'admin.surat.index', 'prefix'=>'admin.surat', 'label'=>'Surat', 'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

@endif

{{-- ==================== SYSADMIN ==================== --}}
@if($role === 'sysadmin')

<a href="{{ route('sysadmin.dashboard') }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active('sysadmin.dashboard') }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <span class="sidebar-text">Dashboard</span>
</a>

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Sistem</p>
</div>
@foreach([
['route'=>'sysadmin.users.index', 'prefix'=>'sysadmin.users', 'label'=>'Manajemen User', 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
['route'=>'sysadmin.activity-log', 'prefix'=>'sysadmin.activity-log', 'label'=>'Activity Log', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
['route'=>'sysadmin.ai-log', 'prefix'=>'sysadmin.ai-log', 'label'=>'AI Log', 'icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">Data</p>
</div>
@foreach([
['route'=>'admin.santri.index', 'prefix'=>'admin.santri', 'label'=>'Santri', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
['route'=>'admin.pendidik.index', 'prefix'=>'admin.pendidik', 'label'=>'Tenaga Pendidik','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
['route'=>'admin.tahun-ajaran.index', 'prefix'=>'admin.tahun-ajaran', 'label'=>'Tahun Ajaran', 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
['route'=>'admin.tingkatan.index', 'prefix'=>'admin.tingkatan', 'label'=>'Tingkatan', 'icon'=>'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

@endif

{{-- ==================== SIMAQ (TAHFIZH) ==================== --}}
@if(Auth::user()->hasRole(['guru_tahsin_tahfizh', 'admin', 'super_admin']) || $role === 'guru_tahsin_tahfizh')

<div class="pt-4 pb-1">
    <p class="px-3 text-[10px] font-semibold uppercase tracking-widest sidebar-section-title"
        style="color: var(--text-secondary); opacity: 0.6;">SIMAQ - Tahfizh</p>
</div>

@foreach([
    ['route'=>'simaq.dashboard', 'prefix'=>'simaq.dashboard', 'label'=>'Dashboard SIMAQ', 'icon'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
    ['route'=>'simaq.harian.index', 'prefix'=>'simaq.harian', 'label'=>'Setoran Harian', 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ['route'=>'simaq.pemantapan.index', 'prefix'=>'simaq.pemantapan', 'label'=>'Ujian Pemantapan', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['route'=>'simaq.tasmi.index', 'prefix'=>'simaq.tasmi', 'label'=>'Imtihan Tasmi\'', 'icon'=>'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
    ['route'=>'simaq.huffazh.index', 'prefix'=>'simaq.huffazh', 'label'=>'J. Huffazh', 'icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
] as $item)
<a href="{{ route($item['route']) }}"
    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $active($item['prefix']) }}"
    style="color: var(--text-secondary);">
    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}" />
    </svg>
    <span class="sidebar-text">{{ $item['label'] }}</span>
</a>
@endforeach

@endif