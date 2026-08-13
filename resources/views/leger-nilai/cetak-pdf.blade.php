<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Leger — {{ $kelas->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1B3C53;
        }

        /* Header -- mPDF TIDAK mendukung display:flex sama sekali.
           Layout sejajar horizontal (logo | judul | info-box) DIPAKSA
           pakai <table>, bukan flex. JANGAN kembalikan ke flex walau
           kelihatan lebih modern di browser -- di mPDF semua child flex
           akan collapse jadi tumpukan vertikal satu kolom. */
        .top-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .top-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .logo-cell {
            width: 65px;
        }

        .logo-box {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            background: #f0f4f7;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 16px;
            color: #1B3C53;
        }

        .title h1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title h2 {
            font-size: 12px;
            font-weight: bold;
            color: #234C6A;
            margin-top: 1px;
        }

        .info-box {
            border: 1px solid #234C6A;
            width: 220px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 3px 6px;
            font-size: 9px;
            border: 1px solid #c7d2da;
        }

        .info-box td.label {
            font-weight: bold;
            width: 80px;
            background: #eef2f5;
        }

        /* Tabel utama */
        /* width: 100% (BUKAN "auto") -- sudah pernah dicoba table-layout:auto +
       width:auto untuk kelas dengan sedikit mapel supaya kolom tidak
       kegedean, TAPI mPDF (beda dari browser sungguhan) salah hitung lebar
       kolom jadi nyaris 0px waktu dikombinasikan dengan header rotate
       (writing-mode: vertical-rl) -- akibatnya tiap huruf ke-wrap sendiri-
       sendiri ke baris baru, hasil PDF jadi rusak total. width:100% lebih
       aman: tabel jadi lega/proporsional kalau mapelnya sedikit, tapi
       SELALU terbaca benar. Kalau nanti mau ngecilin ukuran tanpa risiko
       serupa, lakukan lewat page format (A4-L untuk kelas sedikit mapel)
       atau font-size, JANGAN table-layout:auto lagi di mPDF. */
        table.leger {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.leger th,
        table.leger td {
            border: 1px solid #7a8a95;
            padding: 2px 3px;
            text-align: center;
        }

        table.leger thead th {
            background: #234C6A;
            color: white;
            font-size: 8px;
            font-weight: 600;
            word-wrap: break-word;
        }

        table.leger td.nama {
            text-align: left;
            white-space: nowrap;
            font-size: 8.5px;
        }

        table.leger td.kkm-row {
            background: #eef2f5;
            font-weight: bold;
        }

        table.leger tfoot td {
            background: #1B3C53;
            color: white;
            font-weight: bold;
        }

        table.leger td.dibawah-kkm {
            color: #b91c1c;
            font-weight: bold;
        }

        /* Rotasi header kolom mapel/absensi/kepribadian -- writing-mode
           dan transform TIDAK dipakai di sini karena mPDF tidak
           mendukung keduanya (hanya jalan di browser). Rotasi
           sebenarnya diterapkan lewat atribut inline mpdf-text-rotate
           di tiap <th class="rotate"> di HTML bawah, karena properti
           proprietary mPDF itu HARUS inline, tidak bisa di-cascade dari
           class di sini. Baris di bawah cuma atur tinggi & perataan sel. */
        table.leger th.rotate {
            white-space: nowrap;
            height: 60px;
            vertical-align: bottom;
        }

        /* Tanda tangan -- sama seperti .top, di-table-kan karena mPDF
           tidak dukung flex. Dua kolom dibuat lewat 2 <td>: kiri rata
           kiri, kanan rata kanan (margin-left:auto di dalam <td> kanan)
           supaya efeknya mirip justify-content: space-between. */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            font-size: 9px;
        }

        .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .ttd-col {
            text-align: center;
            width: 160px;
        }

        .ttd-title {
            font-weight: 600;
            margin-bottom: 55px;
        }

        .ttd-line {
            border-top: 1px solid #1B3C53;
            padding-top: 3px;
            font-weight: bold;
            margin-top: 55px;
        }
    </style>
</head>

<body>

    <table class="top-table">
        <tr>
            <td class="logo-cell">
                <div class="logo-box">{{ strtoupper(substr(config('siak.pondok.nama', 'PP'), 0, 2)) }}</div>
            </td>
            <td class="title">
                <h1>Daftar Kumpulan Nilai Semester</h1>
                <h2>{{ config('siak.pondok.nama') }}</h2>
            </td>
            <td style="width: 220px;">
                <div class="info-box">
                    <table>
                        <tr>
                            <td class="label">Kelas</td>
                            <td>{{ $kelas->nama }}</td>
                        </tr>
                        <tr>
                            <td class="label">Semester</td>
                            <td>{{ $ta->semester ? ucfirst($ta->semester) : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tahun Ajaran</td>
                            <td>{{ $ta->nama }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if($data['mapelList']->isEmpty())
    <p style="padding: 20px 0;">Belum ada mata pelajaran yang ditugaskan ke kelas ini.</p>
    @else
    <table class="leger">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">No</th>
                <th rowspan="2" style="width: 80px;">Induk</th>
                <th rowspan="2" style="min-width: 100px;">Nama Santri</th>
                <th rowspan="2" style="min-width: 100px;">Nama Arab</th>
                @foreach($data['mapelList'] as $mapel)
                <th rowspan="2" class="rotate" style="mpdf-text-rotate: 90; width: 20px;">{{ $mapel->nama }}</th>
                @endforeach
                <th rowspan="2" style="width: 40px;">Jumlah</th>
                <th rowspan="2" style="width: 24px;">Rata²</th>
                <th rowspan="2" style="width: 24px;">Peringkat</th>
                <th colspan="3">Ketidakhadiran (Hari)</th>
                <th colspan="4">Kepribadian</th>
            </tr>
            <tr>
                @foreach(['S','I','A'] as $h)<th class="rotate" style="mpdf-text-rotate: 90; width: 16px;">{{ $h }}</th>@endforeach
                @foreach(['Akhlaq','Kerajinan','Kebersihan','Kedisiplinan'] as $h)
                <th class="rotate" style="mpdf-text-rotate: 90; width: 16px;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold; background:#eef2f5;">KKM</td>
                @foreach($data['mapelList'] as $mapel)
                <td class="kkm-row">{{ $data['kkmPerMapel'][$mapel->id] ?? '—' }}</td>
                @endforeach
                <td colspan="10" class="kkm-row"></td>
            </tr>
            @foreach($data['rows'] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['santri']->nis }}</td>
                <td class="nama">{{ $row['santri']->nama_lengkap }}</td>
                <td dir="rtl">{{ $row['santri']->nama_arab ?? '-' }}</td>
                @foreach($data['mapelList'] as $mapel)
                @php
                $nilai = $row['nilai'][$mapel->id] ?? null;
                $kkm = $data['kkmPerMapel'][$mapel->id] ?? null;
                $dibawahKkm = $nilai !== null && $kkm !== null && $nilai < $kkm;
                    @endphp
                    <td class="{{ $dibawahKkm ? 'dibawah-kkm' : '' }}">
                    {{ $nilai !== null ? number_format($nilai, 0) : '-' }}
                    </td>
                    @endforeach
                    <td style="font-weight:bold;">{{ number_format($row['jumlah'], 0) }}</td>
                    <td>{{ $row['rata_rata'] }}</td>
                    <td>{{ $row['peringkat'] ?? '-' }}</td>
                    <td>{{ $row['kehadiran']['sakit'] ?? 0 }}</td>
                    <td>{{ $row['kehadiran']['izin'] ?? 0 }}</td>
                    <td>{{ $row['kehadiran']['alpa'] ?? 0 }}</td>
                    <td>{{ $row['kepribadian']['akhlaq'] }}</td>
                    <td>{{ $row['kepribadian']['kerajinan'] }}</td>
                    <td>{{ $row['kepribadian']['kebersihan'] }}</td>
                    <td>{{ $row['kepribadian']['kedisiplinan'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">RATA-RATA</td>
                @php
                $rataPerMapel = [];
                foreach ($data['mapelList'] as $mapel) {
                $nilaiList = collect($data['rows'])
                ->pluck("nilai.{$mapel->id}")
                ->filter(fn($v) => $v !== null);
                $rataPerMapel[$mapel->id] = $nilaiList->isNotEmpty()
                ? round($nilaiList->avg(), 0) : '-';
                }
                @endphp
                @foreach($data['mapelList'] as $mapel)
                <td>{{ $rataPerMapel[$mapel->id] }}</td>
                @endforeach
                <td colspan="10"></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <table class="ttd-table">
        <tr>
            <td style="text-align: left;">
                <div class="ttd-col" style="margin-left: 0;">
                    <p class="ttd-title">Wali Kelas</p>
                    <p class="ttd-line">{{ $kelas->waliKelas?->name ?? '_______________' }}</p>
                </div>
            </td>
            <td style="text-align: right;">
                <div class="ttd-col" style="margin-left: auto;">
                    <p class="ttd-title">{{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>Kepala Sekolah</p>
                    <p class="ttd-line">{{ config('siak.pondok.kepala', '_______________') }}</p>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>