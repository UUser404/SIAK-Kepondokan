{{-- ============================================================ --}}
{{-- resources/views/leger-nilai/export-excel.blade.php          --}}
{{-- Khusus dipakai App\Exports\LegerNilaiExport (FromView).       --}}
{{-- SENGAJA tabel HTML polos, tanpa Tailwind/style kompleks --     --}}
{{-- Maatwebsite\Excel parse ini jadi cell Excel sungguhan, bukan  --}}
{{-- di-screenshot, jadi struktur <table><tr><td> harus bersih.    --}}
{{-- JANGAN pakai <x-app-layout> di sini -- file ini bukan halaman --}}
{{-- web, cuma sumber data buat generator xlsx.                    --}}
{{-- ============================================================ --}}
<table>
    <thead>
        <tr>
            <th colspan="{{ 14 + $data['mapelList']->count() }}">
                DAFTAR KUMPULAN NILAI SEMESTER — {{ $kelas->nama }} — {{ $ta->nama }} ({{ ucfirst($ta->semester) }})
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Induk</th>
            <th>Nama Santri</th>
            <th>Nama Arab</th>
            @foreach($data['mapelList'] as $mapel)
            <th>{{ $mapel->nama }}</th>
            @endforeach
            <th>Jumlah</th>
            <th>Rata-rata</th>
            <th>Peringkat</th>
            <th>Sakit</th>
            <th>Izin</th>
            <th>Tanpa Keterangan</th>
            <th>Akhlaq</th>
            <th>Kerajinan</th>
            <th>Kebersihan</th>
            <th>Kedisiplinan</th>
        </tr>
        <tr>
            <th colspan="4">KKM</th>
            @foreach($data['mapelList'] as $mapel)
            <th>{{ $data['kkmPerMapel'][$mapel->id] ?? '' }}</th>
            @endforeach
            <th colspan="10"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['rows'] as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['santri']->nis }}</td>
            <td>{{ $row['santri']->nama_lengkap }}</td>
            <td>{{ $row['santri']->nama_arab ?? '' }}</td>
            @foreach($data['mapelList'] as $mapel)
            <td>{{ $row['nilai'][$mapel->id] ?? '' }}</td>
            @endforeach
            <td>{{ $row['jumlah'] }}</td>
            <td>{{ $row['rata_rata'] }}</td>
            <td>{{ $row['peringkat'] ?? '' }}</td>
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
</table>