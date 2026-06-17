<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rapor — {{ $santri->nama_lengkap }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10px;
        color: #1B3C53;
        background: #fff;
    }

    /* Header */
    .header {
        background: linear-gradient(135deg, #1B3C53 0%, #234C6A 100%);
        color: white;
        padding: 20px 30px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .header-logo {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        color: white;
        text-align: center;
        line-height: 50px;
    }
    .header-info h1 {
        font-size: 16px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .header-info p {
        font-size: 9px;
        opacity: 0.85;
        margin-top: 2px;
    }
    .header-ta {
        margin-left: auto;
        text-align: right;
    }
    .header-ta .ta-label { font-size: 8px; opacity: 0.7; }
    .header-ta .ta-value { font-size: 11px; font-weight: bold; }

    /* Divider */
    .divider {
        height: 4px;
        background: linear-gradient(90deg, #1B3C53, #456882, #E3E3E3);
    }

    /* Content */
    .content { padding: 20px 30px; }

    /* Judul rapor */
    .rapor-title {
        text-align: center;
        padding: 12px 0;
        border-bottom: 2px solid #234C6A;
        margin-bottom: 16px;
    }
    .rapor-title h2 {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #1B3C53;
    }
    .rapor-title p {
        font-size: 9px;
        color: #456882;
        margin-top: 2px;
    }

    /* Biodata */
    .biodata {
        display: flex;
        gap: 20px;
        margin-bottom: 16px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #E3E3E3;
        border-radius: 6px;
    }
    .biodata-col { flex: 1; }
    .biodata-row {
        display: flex;
        gap: 8px;
        margin-bottom: 4px;
        font-size: 9px;
    }
    .biodata-label {
        width: 80px;
        color: #456882;
        flex-shrink: 0;
    }
    .biodata-value {
        font-weight: 600;
        color: #1B3C53;
    }

    /* Summary boxes */
    .summary {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
    }
    .summary-box {
        flex: 1;
        text-align: center;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #E3E3E3;
    }
    .summary-box .val { font-size: 18px; font-weight: bold; color: #1B3C53; }
    .summary-box .lbl { font-size: 8px; color: #456882; margin-top: 2px; }
    .summary-box.green { background: #f0fdf4; border-color: #bbf7d0; }
    .summary-box.green .val { color: #15803d; }
    .summary-box.blue  { background: #eff6ff; border-color: #bfdbfe; }
    .summary-box.blue  .val { color: #1d4ed8; }
    .summary-box.yellow{ background: #fefce8; border-color: #fef08a; }
    .summary-box.yellow .val { color: #a16207; }

    /* Tabel nilai */
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th {
        background: #1B3C53;
        color: white;
        padding: 6px 4px;
        text-align: center;
        font-size: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #456882;
    }
    th.left { text-align: left; padding-left: 8px; }
    td {
        padding: 5px 4px;
        text-align: center;
        font-size: 9px;
        border: 1px solid #E3E3E3;
        color: #1B3C53;
    }
    td.left { text-align: left; padding-left: 8px; font-weight: 600; }
    tr:nth-child(even) td { background: #f8fafc; }
    tr.tidak-tuntas td { background: #fff5f5; }
    .nilai-akhir { font-weight: bold; font-size: 10px; }
    .tuntas   { color: #15803d; font-weight: bold; }
    .belum    { color: #dc2626; font-weight: bold; }
    .predikat { font-weight: bold; font-size: 11px; }

    /* Tanda tangan */
    .ttd {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        font-size: 9px;
    }
    .ttd-col { text-align: center; width: 160px; }
    .ttd-title { font-weight: 600; color: #1B3C53; margin-bottom: 50px; }
    .ttd-line {
        border-top: 1px solid #1B3C53;
        padding-top: 4px;
        font-weight: bold;
        color: #1B3C53;
    }
    .ttd-nip { color: #456882; font-size: 8px; }

    /* Footer */
    .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #E3E3E3;
        display: flex;
        justify-content: space-between;
        font-size: 8px;
        color: #456882;
    }

    /* Page break prevention */
    tr { page-break-inside: avoid; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-logo">PP</div>
    <div class="header-info">
        <h1>{{ config('siak.pondok.nama') }}</h1>
        <p>{{ config('siak.pondok.alamat', 'Alamat Pondok') }}</p>
        <p>Telp: {{ config('siak.pondok.telp', '-') }} | Email: {{ config('siak.pondok.email', '-') }}</p>
    </div>
    <div class="header-ta">
        <p class="ta-label">Tahun Ajaran</p>
        <p class="ta-value">{{ $ta?->nama_lengkap ?? '-' }}</p>
    </div>
</div>
<div class="divider"></div>

<div class="content">

    {{-- Judul --}}
    <div class="rapor-title">
        <h2>Laporan Hasil Belajar Santri</h2>
        <p>{{ $ta?->nama_lengkap }}</p>
    </div>

    {{-- Biodata --}}
    <div class="biodata">
        <div class="biodata-col">
            @foreach([
                ['Nama Lengkap', $santri->nama_lengkap],
                ['NIS',          $santri->nis],
                ['NISN',         $santri->nisn ?? '-'],
                ['L/P',          $santri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'],
            ] as [$label, $value])
            <div class="biodata-row">
                <span class="biodata-label">{{ $label }}</span>
                <span>:</span>
                <span class="biodata-value">{{ $value }}</span>
            </div>
            @endforeach
        </div>
        <div class="biodata-col">
            @foreach([
                ['Kelas',        $kelasAktif?->nama ?? '-'],
                ['Tingkatan',    $kelasAktif?->tingkatan?->nama ?? '-'],
                ['Wali Kelas',   $kelasAktif?->waliKelas?->name ?? '-'],
                ['Tahun Masuk',  $santri->angkatan ?? '-'],
            ] as [$label, $value])
            <div class="biodata-row">
                <span class="biodata-label">{{ $label }}</span>
                <span>:</span>
                <span class="biodata-value">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Summary --}}
    @php
        $rataRata    = round($nilaiAkhir->avg('nilai_akhir') ?? 0, 1);
        $totalTuntas = $nilaiAkhir->where('tuntas', true)->count();
        $totalMapel  = $nilaiAkhir->count();
        $predikatUmum = $rataRata >= 90 ? 'A' : ($rataRata >= 80 ? 'B' : ($rataRata >= 70 ? 'C' : 'D'));
    @endphp
    <div class="summary">
        <div class="summary-box {{ $rataRata >= 70 ? 'green' : 'yellow' }}">
            <div class="val">{{ $rataRata }}</div>
            <div class="lbl">Rata-rata Nilai</div>
        </div>
        <div class="summary-box {{ $totalTuntas === $totalMapel ? 'green' : 'yellow' }}">
            <div class="val">{{ $totalTuntas }}/{{ $totalMapel }}</div>
            <div class="lbl">Mapel Tuntas</div>
        </div>
        <div class="summary-box blue">
            <div class="val">{{ $predikatUmum }}</div>
            <div class="lbl">Predikat Umum</div>
        </div>
    </div>

    {{-- Tabel nilai --}}
    <table>
        <thead>
            <tr>
                <th class="left" style="width: 30px;">No</th>
                <th class="left" style="width: 120px;">Mata Pelajaran</th>
                <th style="width: 30px;">UH</th>
                <th style="width: 35px;">Tugas</th>
                <th style="width: 35px;">Prak.</th>
                <th style="width: 30px;">UTS</th>
                <th style="width: 30px;">UAS</th>
                <th style="width: 40px;">Akhir</th>
                <th style="width: 28px;">Pred.</th>
                <th style="width: 28px;">KKM</th>
                <th style="width: 35px;">Status</th>
                <th style="width: 22px;">H</th>
                <th style="width: 22px;">S</th>
                <th style="width: 22px;">I</th>
                <th style="width: 22px;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nilaiAkhir as $i => $na)
            @php $keh = $kehadiranMapel[$na->mata_pelajaran_id] ?? null; @endphp
            <tr class="{{ !$na->tuntas ? 'tidak-tuntas' : '' }}">
                <td class="left">{{ $i + 1 }}</td>
                <td class="left">{{ $na->mataPelajaran->nama }}</td>
                <td>{{ $na->nilai_uh ? number_format($na->nilai_uh, 1) : '-' }}</td>
                <td>{{ $na->nilai_tugas ? number_format($na->nilai_tugas, 1) : '-' }}</td>
                <td>{{ $na->nilai_praktik ? number_format($na->nilai_praktik, 1) : '-' }}</td>
                <td>{{ $na->nilai_uts ? number_format($na->nilai_uts, 1) : '-' }}</td>
                <td>{{ $na->nilai_uas ? number_format($na->nilai_uas, 1) : '-' }}</td>
                <td class="nilai-akhir {{ $na->tuntas ? 'tuntas' : 'belum' }}">
                    {{ $na->nilai_akhir ? number_format($na->nilai_akhir, 1) : '-' }}
                </td>
                <td class="predikat">{{ $na->predikat ?? '-' }}</td>
                <td>{{ $na->mataPelajaran->kkm }}</td>
                <td class="{{ $na->tuntas ? 'tuntas' : 'belum' }}">
                    {{ $na->tuntas ? 'Tuntas' : 'Belum' }}
                </td>
                <td>{{ $keh['hadir'] ?? 0 }}</td>
                <td>{{ $keh['sakit'] ?? 0 }}</td>
                <td>{{ $keh['izin']  ?? 0 }}</td>
                <td class="{{ ($keh['alpa'] ?? 0) > 0 ? 'belum' : '' }}">
                    {{ $keh['alpa']  ?? 0 }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda tangan --}}
    <div class="ttd">
        <div class="ttd-col">
            <p class="ttd-title">Mengetahui,<br>Orang Tua / Wali</p>
            <p class="ttd-line">{{ $santri->nama_wali ?? $santri->nama_ayah ?? '_______________' }}</p>
        </div>
        <div class="ttd-col">
            <p class="ttd-title">Wali Kelas</p>
            <p class="ttd-line">{{ $kelasAktif?->waliKelas?->name ?? '_______________' }}</p>
            <p class="ttd-nip">
                {{ $kelasAktif?->waliKelas?->tenagaPendidik?->nip
                    ? 'NIP: ' . $kelasAktif->waliKelas->tenagaPendidik->nip
                    : '' }}
            </p>
        </div>
        <div class="ttd-col">
            <p class="ttd-title">{{ config('siak.pondok.nama') }}<br>Mudir</p>
            <p class="ttd-line">{{ config('siak.pondok.kepala', '_______________') }}</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span>Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</span>
        <span>{{ config('siak.pondok.nama') }} — SIAK Kepondokan</span>
        <span>NIS: {{ $santri->nis }}</span>
    </div>

</div>
</body>
</html>
