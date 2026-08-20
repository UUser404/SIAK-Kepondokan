{{-- ============================================================ --}}
{{-- resources/views/rapor/cetak-arab-pdf.blade.php                --}}
{{-- Rapor Arab - Format "كشف الدرجة", panjang halaman FLEKSIBEL   --}}
{{-- mengikuti jumlah mapel yang diampu murid. Header/footer pakai --}}
{{-- mekanisme native mPDF (<htmlpageheader>/<htmlpagefooter>)     --}}
{{-- supaya otomatis terulang di berapa pun halaman yang terbentuk. --}}
{{-- ============================================================ --}}
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top: 34mm;
            margin-right: 15mm;
            margin-bottom: 20mm;
            margin-left: 15mm;
            margin-header: 8mm;
            margin-footer: 8mm;
        }

        body {
            font-family: 'Traditional Arabic', 'DejaVu Sans', 'Noto Naskh Arabic', sans-serif;
            direction: rtl;
            font-size: 12.5px;
            color: #111;
            line-height: 1.6;
        }

        /* ========== HEADER BERULANG (native mPDF) ========== */
        .header-info {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #333;
            padding-bottom: 4px;
        }

        .header-info td {
            border: none;
            padding: 2px 6px;
            font-size: 12px;
            vertical-align: top;
            width: 50%;
        }

        .header-info .h-label {
            font-weight: bold;
        }

        .header-info .h-sep {
            margin: 0 4px;
        }

        /* ========== FOOTER BERULANG (native mPDF) ========== */
        .footer-rapor {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #999;
            padding-top: 3px;
            font-size: 10.5px;
            color: #444;
        }

        .footer-rapor td {
            border: none;
            padding: 0 2px;
        }

        .footer-rapor .footer-kanan {
            text-align: right;
        }

        .footer-rapor .footer-kiri {
            text-align: left;
        }

        /* ========== JUDUL ========== */
        .judul-utama {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 0 0 2px 0;
        }

        .nama-sekolah {
            text-align: center;
            font-size: 13px;
            margin-bottom: 10px;
        }

        /* ========== TABEL NILAI (mengalir otomatis ke berapa pun halaman) ========== */
        .tabel-nilai {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .tabel-nilai thead {
            display: table-header-group;
        }

        .tabel-nilai th {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
            font-size: 12px;
        }

        .tabel-nilai td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 11.5px;
        }

        .tabel-nilai .mapel-cell {
            text-align: right;
        }

        .tabel-nilai .kategori-row td {
            font-weight: bold;
            text-align: right;
            font-size: 12px;
            padding: 5px 8px;
        }

        .tabel-nilai .deskripsi-cell {
            text-align: right;
            font-size: 10.5px;
            line-height: 1.7;
            padding: 5px 8px;
        }

        .col-no {
            width: 5%;
        }

        .col-mapel {
            width: 20%;
        }

        .col-nilai {
            width: 10%;
        }

        .col-deskripsi {
            width: 65%;
        }

        /* ========== KOTAK SAMPING (شخصيات الطالب / الغياب) ========== */
        .side-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .side-box td {
            border: 1px solid #333;
            padding: 6px 10px;
            font-size: 12px;
        }

        .side-box .title-cell {
            font-weight: bold;
            text-align: center;
            width: 20%;
            vertical-align: middle;
            background-color: #f5f5f5;
        }

        .side-box .label-cell {
            font-weight: bold;
            text-align: right;
            width: 30%;
        }

        .side-box .sep-cell {
            width: 3%;
            text-align: center;
        }

        .side-box .value-cell {
            text-align: center;
            width: 47%;
        }

        /* ========== RINGKASAN ========== */
        .ringkasan {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px 0;
            page-break-inside: avoid;
        }

        .ringkasan td {
            border: 1px solid #333;
            padding: 5px 10px;
            font-size: 12.5px;
        }

        .ringkasan .label-r {
            font-weight: bold;
            text-align: right;
            width: 65%;
        }

        .ringkasan .value-r {
            text-align: center;
            width: 35%;
        }

        /* ========== KESIMPULAN ========== */
        .kesimpulan-box {
            text-align: center;
            margin: 10px 0;
            padding: 4px 0;
            page-break-inside: avoid;
        }

        .kesimpulan-box .kesimpulan-teks {
            font-size: 13px;
            margin-bottom: 4px;
        }

        .kesimpulan-box .status-box {
            display: inline-block;
            border: 1px solid #333;
            padding: 6px 30px;
            font-size: 20px;
            font-weight: bold;
        }

        /* ========== TANGGAL ========== */
        .tanggal-block {
            text-align: right;
            font-size: 12px;
            margin: 14px 0 4px 0;
            page-break-inside: avoid;
        }

        .tanggal-hijriah {
            font-weight: bold;
            text-decoration: underline;
        }

        /* ========== TANDA TANGAN (4 kolom, tampil 1x di akhir dokumen) ========== */
        .ttd {
            margin-top: 18mm;
            page-break-inside: avoid;
        }

        .ttd table {
            width: 100%;
            border-collapse: collapse;
        }

        .ttd td {
            border: none;
            text-align: center;
            padding-top: 34px;
            font-size: 11.5px;
            vertical-align: bottom;
            width: 25%;
        }

        .ttd-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 85%;
            margin-top: 4px;
        }

        .ttd-name {
            font-weight: bold;
            margin-top: 4px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    @php
    // Konversi angka Latin -> angka Arab (٠١٢٣٤٥٦٧٨٩).
    function toArabicDigits($val) {
    if ($val === null || $val === '-') return $val ?? '-';
    return strtr((string) $val, ['0'=>'٠','1'=>'١','2'=>'٢','3'=>'٣','4'=>'٤','5'=>'٥','6'=>'٦','7'=>'٧','8'=>'٨','9'=>'٩']);
    }

    // Format tanggal Masehi manual ke Arab (nama bulan Arab + angka Arab).
    // Tidak mengandalkan translatedFormat()/locale aplikasi karena
    // terbukti tidak konsisten (kadang masih keluar nama bulan Inggris).
    function formatMasehiArab($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
    1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل', 5=>'مايو', 6=>'يونيو',
    7=>'يوليو', 8=>'أغسطس', 9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر',
    ];
    return toArabicDigits($tanggal->day) . ' ' . ($bulan[$tanggal->month] ?? '') . ' ' . toArabicDigits($tanggal->year);
    }

    // Konversi Masehi -> Hijriah murni PHP (algoritma tabular/Kuwaiti),
    // TIDAK bergantung pada ekstensi "calendar" PHP (sering tidak aktif
    // di instalasi Windows). Dipakai sebagai fallback otomatis kalau
    // $data['tanggal_hijriah'] belum diisi manual di database.
    function masehiKeHijriahArab($tanggal) {
    if (!$tanggal) return '-';

    $bulanHijriah = [
    1=>'محرم', 2=>'صفر', 3=>'ربيع الأول', 4=>'ربيع الآخر', 5=>'جمادى الأولى', 6=>'جمادى الآخرة',
    7=>'رجب', 8=>'شعبان', 9=>'رمضان', 10=>'شوال', 11=>'ذو القعدة', 12=>'ذو الحجة',
    ];

    $day = (int) $tanggal->day;
    $month = (int) $tanggal->month;
    $year = (int) $tanggal->year;

    $jd = intval((1461 * ($year + 4800 + intval(($month - 14) / 12))) / 4)
    + intval((367 * ($month - 2 - 12 * intval(($month - 14) / 12))) / 12)
    - intval((3 * intval(($year + 4900 + intval(($month - 14) / 12)) / 100)) / 4)
    + $day - 32075;

    $l = $jd - 1948440 + 10632;
    $n = intval(($l - 1) / 10631);
    $l = $l - 10631 * $n + 354;
    $j = intval((10985 - $l) / 5316) * intval((50 * $l) / 17719)
    + intval($l / 5670) * intval((43 * $l) / 15238);
    $l = $l - intval((30 - $j) / 15) * intval((17719 * $j) / 50)
    - intval($j / 16) * intval((15238 * $j) / 43) + 29;
    $hm = intval((24 * $l) / 709);
    $hd = $l - intval((709 * $hm) / 24);
    $hy = 30 * $n + $j - 30;

    return toArabicDigits($hd) . ' ' . ($bulanHijriah[$hm] ?? '') . ' ' . toArabicDigits($hy) . ' هـ';
    }

    $tanggalMasehiArab = formatMasehiArab($data['tanggal_masehi'] ?? null);
    $tanggalHijriahArab = !empty($data['tanggal_hijriah'])
    ? $data['tanggal_hijriah']
    : masehiKeHijriahArab($data['tanggal_masehi'] ?? null);

    $kategoriMap = [
    'wajib' => 'المواد الإجبارية',
    'Mata Pelajaran Wajib' => 'المواد الإجبارية',
    'pilihan' => 'المواد الاختيارية',
    'Mata Pelajaran Pilihan' => 'المواد الاختيارية',
    'mulok' => 'المحتوى المحلي',
    'muatan_lokal' => 'المحتوى المحلي',
    'Muatan Lokal' => 'المحتوى المحلي',
    ];

    $semesterTeks = $data['ta']->semester === 'ganjil' ? 'الأول' : 'الثاني';
    @endphp

    {{-- ============================================================ --}}
    {{-- DEFINISI HEADER BERULANG (mekanisme native mPDF)             --}}
    {{-- ============================================================ --}}
    <htmlpageheader name="header-rapor">
        <table class="header-info">
            <tr>
                <td>
                    <span class="h-label">إسم الطالب</span><span class="h-sep">:</span>
                    {{ $data['santri']['nama_arab'] ?? $data['santri']['nama_latin'] }}
                </td>
                <td>
                    <span class="h-label">الفصل</span><span class="h-sep">:</span>
                    {{ $data['kelas']->nama }}
                </td>
            </tr>
            <tr>
                <td>
                    <span class="h-label">رقم قيد الطالب</span><span class="h-sep">:</span>
                    {{ toArabicDigits($data['santri']['nis']) }}
                </td>
                <td>
                    <span class="h-label">الفصل الدراسي</span><span class="h-sep">:</span>
                    {{ $semesterTeks }}
                </td>
            </tr>
            <tr>
                <td>
                    <span class="h-label">العام الدراسي</span><span class="h-sep">:</span>
                    {{ toArabicDigits($data['ta']->nama) }}
                </td>
                <td>
                    <span class="h-label">القسم</span><span class="h-sep">:</span>
                    الشرعي
                </td>
            </tr>
        </table>
    </htmlpageheader>

    {{-- ============================================================ --}}
    {{-- DEFINISI FOOTER BERULANG (mekanisme native mPDF)             --}}
    {{-- {PAGENO} dan {nbpg} otomatis diisi mPDF (halaman saat ini/total) --}}
    {{-- ============================================================ --}}
    <htmlpagefooter name="footer-rapor">
        <table class="footer-rapor">
            <tr>
                <td class="footer-kanan">
                    {{ $data['kelas']->nama }} | {{ $data['santri']['nama_arab'] ?? $data['santri']['nama_latin'] }} | {{ toArabicDigits($data['santri']['nis']) }}
                </td>
                <td class="footer-kiri">الصفحة {PAGENO} من {nbpg}</td>
            </tr>
        </table>
    </htmlpagefooter>

    <sethtmlpageheader name="header-rapor" value="on" show-this-page="1" />
    <sethtmlpagefooter name="footer-rapor" value="on" />

    {{-- ============================================================ --}}
    {{-- JUDUL (sekali saja, di awal alur dokumen)                    --}}
    {{-- ============================================================ --}}
    <div class="judul-utama">كشف الدرجة</div>
    <div class="nama-sekolah">كلية المعلمين الإسلامية بـ{{ $data['sekolah_nama'] ?? 'معهد الإسلام الإسلامي للتربية الإسلامية الحديثة' }}</div>

    {{-- ============================================================ --}}
    {{-- TABEL NILAI (No, Mapel, Nilai Akhir, Deskripsi) - kategori   --}}
    {{-- jadi 1 baris penuh, mengalir otomatis sesuai jumlah mapel    --}}
    {{-- ============================================================ --}}
    <table class="tabel-nilai">
        <thead>
            <tr>
                <th class="col-no">الرقم</th>
                <th class="col-mapel">المواد الدراسية</th>
                <th class="col-nilai">الدرجة النهائية</th>
                <th class="col-deskripsi">وصفيات النتائج / وصف الإنجاز</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($data['mapel_per_kategori'] as $kategori => $daftarMapel)
            <tr class="kategori-row">
                <td colspan="4">{{ $kategoriMap[$kategori] ?? $kategori }}</td>
            </tr>
            @foreach($daftarMapel as $m)
            <tr>
                <td class="col-no">{{ toArabicDigits($no++) }}</td>
                <td class="mapel-cell">{{ $m['nama'] }}</td>
                <td>{{ $m['nilai_angka'] !== null ? toArabicDigits(number_format($m['nilai_angka'], 0)) : '-' }}</td>
                <td class="deskripsi-cell">{{ $m['deskripsi'] }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- شخصيات الطالب --}}
    <table class="side-box">
        <tr>
            <td class="title-cell" rowspan="4">شخصيات الطالب</td>
            <td class="label-cell">الأدب</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ $data['kepribadian']['akhlaq'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">المواظبة</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ $data['kepribadian']['kerajinan'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">النظافة</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ $data['kepribadian']['kebersihan'] }}</td>
        </tr>
        <tr>
            <td class="label-cell">الإنضباط</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ $data['kepribadian']['kedisiplinan'] }}</td>
        </tr>
    </table>

    {{-- الغياب --}}
    <table class="side-box">
        <tr>
            <td class="title-cell" rowspan="3">الغياب</td>
            <td class="label-cell">لمرض</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ toArabicDigits($data['ketidakhadiran']['sakit']) }} حصة</td>
        </tr>
        <tr>
            <td class="label-cell">بإذن</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ toArabicDigits($data['ketidakhadiran']['izin']) }} حصة</td>
        </tr>
        <tr>
            <td class="label-cell">بلا إذن</td>
            <td class="sep-cell">:</td>
            <td class="value-cell">{{ toArabicDigits($data['ketidakhadiran']['alpa']) }} حصة</td>
        </tr>
    </table>

    {{-- KESIMPULAN --}}
    <div class="kesimpulan-box">
        <div class="kesimpulan-teks">اعتمادا على النتائج المذكورة نقرر بأن الطالب المذكورة</div>
        <div class="status-box">" {{ $data['kesimpulan'] }} "</div>
    </div>

    {{-- RINGKASAN --}}
    <table class="ringkasan">
        <tr>
            <td class="label-r">مجموع الدرجات</td>
            <td class="value-r">{{ toArabicDigits(number_format($data['jumlah'], 0)) }}</td>
        </tr>
        <tr>
            <td class="label-r">المعدل الدرجات</td>
            <td class="value-r">{{ toArabicDigits($data['rata_rata']) }}</td>
        </tr>
        <tr>
            <td class="label-r">رتبته فى مستواه</td>
            <td class="value-r">{{ $data['peringkat_tampil'] !== null ? toArabicDigits($data['peringkat_tampil']) : '-' }}</td>
        </tr>
        <tr>
            <td class="label-r">عدد الطلبة فى الفصل</td>
            <td class="value-r">{{ toArabicDigits($data['jumlah_santri_kelas']) }}</td>
        </tr>
    </table>

    {{-- TANGGAL --}}
    <div class="tanggal-block">
        تحريرا بـ{{ $data['tempat'] ?? '-' }}،<br>
        <span class="tanggal-hijriah">{{ $tanggalHijriahArab }}</span><br>
        <span>{{ $tanggalMasehiArab }}</span>
    </div>

    {{-- TANDA TANGAN (4 kolom, urut kanan ke kiri: mudir, kepala sekolah, wali kelas, wali santri) --}}
    <div class="ttd">
        <table>
            <tr>
                <td>
                    بمعرفة من: مدير المعهد
                    <div class="ttd-line"></div>
                    <div class="ttd-name">{{ $data['mudir'] ?? '-' }}</div>
                </td>
                <td>
                    رئيس المدرسة
                    <div class="ttd-line"></div>
                    <div class="ttd-name">{{ $data['kepala_sekolah'] ?? '-' }}</div>
                </td>
                <td>
                    ولي الفصل
                    <div class="ttd-line"></div>
                    <div class="ttd-name">{{ $data['wali_kelas'] ?? '-' }}</div>
                </td>
                <td>
                    ولي الطالب
                    <div class="ttd-line"></div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>