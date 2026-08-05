{{-- ============================================================ --}}
{{-- resources/views/rapor/cetak-arab-pdf.blade.php                --}}
{{-- Rapor 2 halaman format Arab (KMI) -- render lewat DomPDF.      --}}
{{-- Struktur & rumus mengikuti PERSIS sheet "RAPORT" pada template --}}
{{-- asli yang diberikan pengguna (RAPORT_7A_GANJIL_2025_2026.xlsx). --}}
{{-- ============================================================ --}}
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 12mm 14mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Noto Naskh Arabic', sans-serif;
            direction: rtl;
            font-size: 11px;
            color: #111;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 3px 5px;
            text-align: center;
            vertical-align: middle;
        }

        .no-border td,
        .no-border th {
            border: none;
        }

        h1 {
            font-size: 15px;
            text-align: center;
            margin: 0 0 2px 0;
        }

        h2 {
            font-size: 12px;
            text-align: center;
            margin: 0 0 10px 0;
            font-weight: normal;
        }

        .header-info td {
            border: none;
            padding: 2px 4px;
            font-size: 11px;
        }

        .header-info .label {
            font-weight: bold;
            width: 90px;
        }

        .kategori-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .ttd-table td {
            border: none;
            text-align: center;
            padding-top: 40px;
            font-size: 11px;
        }

        .ttd-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 80%;
            margin-top: 2px;
        }

        .small {
            font-size: 9px;
            color: #444;
        }
    </style>
</head>

<body>

    {{-- ===================== HALAMAN 1 ===================== --}}
    <h1>كشف الدرجة</h1>
    <h2>كلية المعلمين الإسلامية بمعهد الإسلام للتربية الإسلامية الحديثة</h2>

    <table class="header-info no-border">
        <tr>
            <td class="label">اسم الطالب</td>
            <td>{{ $data['santri']['nama_arab'] ?? $data['santri']['nama_latin'] }}</td>
            <td class="label">الفصل</td>
            <td>{{ $data['kelas']->nama }}</td>
        </tr>
        <tr>
            <td class="label">رقم قيد الطالب</td>
            <td>{{ $data['santri']['nis'] }}</td>
            <td class="label">الفصل الدراسي</td>
            <td>{{ $data['ta']->semester === 'ganjil' ? 'آخر الدراسي الأول' : 'آخر الدراسي الثاني' }}</td>
        </tr>
        <tr>
            <td class="label">العام الدراسي</td>
            <td>{{ $data['ta']->nama }}</td>
            <td class="label">القسم</td>
            <td>الشرعي</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:4%;">الرقم</th>
                <th rowspan="2" style="width:14%;">أقسام المواد الدراسية</th>
                <th rowspan="2">المواد الدراسية</th>
                <th style="width:8%;">الحد الأدنى</th>
                <th colspan="2">الفرجات المكتسبة</th>
                <th rowspan="2" style="width:14%;">التقدير</th>
                <th rowspan="2" style="width:8%;">المعدل الفصل</th>
            </tr>
            <tr>
                <th style="width:8%;">معايير إكتمال</th>
                <th style="width:8%;">رقما</th>
                <th style="width:14%;">كتابة</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['mapel_per_kategori'] as $kategori => $daftarMapel)
            @foreach($daftarMapel as $i => $m)
            <tr>
                @if($i === 0)
                <td rowspan="{{ $daftarMapel->count() }}">{{ $no++ }}</td>
                <td rowspan="{{ $daftarMapel->count() }}">{{ $kategori }}</td>
                @endif
                <td style="text-align:right;">{{ $m['nama'] }}</td>
                <td>{{ $m['kkm'] ?? '-' }}</td>
                <td>{{ $m['nilai_angka'] !== null ? number_format($m['nilai_angka'], 0) : '-' }}</td>
                <td>{{ $m['nilai_kata'] }}</td>
                <td>{{ $m['predikat'] }}</td>
                <td>{{ $m['nilai_angka'] !== null ? number_format($m['nilai_angka'], 0) : '-' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <table class="no-border" style="margin-top:6px;">
        <tr>
            <td style="text-align:right; width:70%;">مجموع الدرجات</td>
            <td style="text-align:left;">{{ number_format($data['jumlah'], 0) }}</td>
        </tr>
        <tr>
            <td style="text-align:right;">المعدل</td>
            <td style="text-align:left;">{{ $data['rata_rata'] }}</td>
        </tr>
        <tr>
            <td style="text-align:right;">رتبته في مستواه</td>
            <td style="text-align:left;">{{ $data['peringkat_tampil'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="text-align:right;">عدد الطلبة في الفصل</td>
            <td style="text-align:left;">{{ $data['jumlah_santri_kelas'] }}</td>
        </tr>
    </table>

    <table class="no-border" style="margin-top:14px;">
        <tr>
            <td style="text-align:center; width:50%;">
                {{ $data['tanggal_hijriah'] ?? '-' }}
            </td>
            <td style="text-align:center;">
                {{ optional($data['tanggal_masehi'])->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center; padding-top:2px;">تحريرا بثيرون</td>
        </tr>
    </table>

    <table class="ttd-table" style="margin-top:10px;">
        <tr>
            <td style="width:33%;">
                ولي الطالب
                <div class="ttd-line"></div>
            </td>
            <td style="width:34%;">
                ولي الفصل
                <div class="ttd-line"></div>
                <div>{{ $data['wali_kelas'] ?? '-' }}</div>
            </td>
            <td style="width:33%;">
                رئيسة المدرسة
                <div class="ttd-line"></div>
                <div>{{ $data['kepala_sekolah'] ?? '-' }}</div>
            </td>
        </tr>
    </table>
    <table class="no-border">
        <tr>
            <td style="text-align:center; padding-top:20px;">
                معرفة من : مدير المعهد
                <div class="ttd-line" style="margin: 4px auto;"></div>
                <div>{{ $data['mudir'] ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- ===================== HALAMAN 2 ===================== --}}
    <h1 class="small" style="text-align:left;">{{ $data['santri']['nama_latin'] }} — {{ $data['kelas']->nama }}</h1>

    <table class="header-info no-border">
        <tr>
            <td class="label">اسم الطالب</td>
            <td>{{ $data['santri']['nama_arab'] ?? $data['santri']['nama_latin'] }}</td>
        </tr>
        <tr>
            <td class="label">الفصل</td>
            <td>{{ $data['kelas']->nama }}</td>
            <td class="label">الفصل الدراسي</td>
            <td>{{ $data['ta']->semester === 'ganjil' ? 'آخر الدراسي الأول' : 'آخر الدراسي الثاني' }}</td>
        </tr>
        <tr>
            <td class="label">العام الدراسي</td>
            <td>{{ $data['ta']->nama }}</td>
            <td class="label">القسم</td>
            <td>الشرعي</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">الرقم</th>
                <th style="width:14%;">أقسام المواد الدراسية</th>
                <th style="width:16%;">المواد الدراسية</th>
                <th>وصفيات النتائج</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['mapel_per_kategori'] as $kategori => $daftarMapel)
            @foreach($daftarMapel as $i => $m)
            <tr>
                @if($i === 0)
                <td rowspan="{{ $daftarMapel->count() }}">{{ $no++ }}</td>
                <td rowspan="{{ $daftarMapel->count() }}">{{ $kategori }}</td>
                @endif
                <td style="text-align:right;">{{ $m['nama'] }}</td>
                <td style="text-align:right;">{{ $m['deskripsi'] }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    <table style="margin-top:10px;">
        <tr>
            <th rowspan="4" style="width:16%;">شخصيات الطالب</th>
            <th style="width:14%;">الأدب</th>
            <td>{{ $data['kepribadian']['akhlaq'] }}</td>
        </tr>
        <tr>
            <th>المواظبة</th>
            <td>{{ $data['kepribadian']['kerajinan'] }}</td>
        </tr>
        <tr>
            <th>النظافة</th>
            <td>{{ $data['kepribadian']['kebersihan'] }}</td>
        </tr>
        <tr>
            <th>الإنضباط</th>
            <td>{{ $data['kepribadian']['kedisiplinan'] }}</td>
        </tr>
    </table>

    <table style="margin-top:6px;">
        <tr>
            <th rowspan="3" style="width:16%;">الغياب</th>
            <th style="width:14%;">لمرض</th>
            <td>{{ $data['ketidakhadiran']['sakit'] }} حصة</td>
        </tr>
        <tr>
            <th>بإذن</th>
            <td>{{ $data['ketidakhadiran']['izin'] }} حصة</td>
        </tr>
        <tr>
            <th>بلا إذن</th>
            <td>{{ $data['ketidakhadiran']['alpa'] }} حصة</td>
        </tr>
    </table>

    <table class="no-border" style="margin-top:12px;">
        <tr>
            <td style="text-align:center;">
                اعتمادا على النتائج المذكورة نقرر بأن الطالب المذكورة
                <div style="font-size:16px; font-weight:bold; margin-top:4px;">" {{ $data['kesimpulan'] }} "</div>
            </td>
        </tr>
    </table>

    <table class="no-border" style="margin-top:8px;">
        <tr>
            <td style="text-align:center; width:50%;">{{ $data['tanggal_hijriah'] ?? '-' }}</td>
            <td style="text-align:center;">{{ optional($data['tanggal_masehi'])->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center; padding-top:2px;">تحريرا بثيرون</td>
        </tr>
    </table>

    <table class="ttd-table" style="margin-top:10px;">
        <tr>
            <td style="width:33%;">
                ولي الطالب
                <div class="ttd-line"></div>
            </td>
            <td style="width:34%;">
                ولي الفصل
                <div class="ttd-line"></div>
                <div>{{ $data['wali_kelas'] ?? '-' }}</div>
            </td>
            <td style="width:33%;">
                رئيسة المدرسة
                <div class="ttd-line"></div>
                <div>{{ $data['kepala_sekolah'] ?? '-' }}</div>
            </td>
        </tr>
    </table>

</body>

</html>