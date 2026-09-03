<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor SIMAQ - {{ $santri->nama ?? $santri->nama_lengkap }}</title>
    <!-- Memanggil Tailwind -->
    @vite(['resources/css/app.css'])
    <style>
        /* Pengaturan Kertas Print A4 */
        @page { size: A4; margin: 2cm; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        .kop-border { border-bottom: 4px solid #000; margin-bottom: 2px; }
        .kop-border-thin { border-bottom: 1px solid #000; margin-bottom: 20px; }
    </style>
</head>
<body class="bg-white text-black font-serif text-sm">

    <!-- Tombol Print Manual (Sembunyi saat diprint) -->
    <div class="text-center my-4 no-print">
        <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white font-bold rounded shadow-lg hover:bg-blue-700">PRINT RAPOR SEKARANG</button>
    </div>

    <div class="max-w-[21cm] mx-auto bg-white p-8">
        
        <!-- KOP SURAT PONDOK -->
        <table class="w-full text-center">
            <tr>
                <td class="w-24 align-middle">
                    <!-- Pastikan logo ada di folder public/images/logo.png sesuai config siak.php -->
                    <img src="{{ asset($pondok['logo'] ?? 'images/logo.png') }}" alt="Logo" class="w-20 h-20 object-contain mx-auto">
                </td>
                <td class="align-middle">
                    <h1 class="text-xl font-bold uppercase tracking-widest">{{ $pondok['nama'] ?? 'PONDOK PESANTREN MODERN' }}</h1>
                    <h2 class="text-lg font-bold">LEMBAGA TAHFIZH & TAHSIN AL-QUR'AN (SIMAQ)</h2>
                    <p class="text-xs mt-1">{{ $pondok['alamat'] ?? 'Alamat Pondok Pesantren' }}</p>
                </td>
            </tr>
        </table>
        <div class="kop-border mt-3"></div>
        <div class="kop-border-thin"></div>

        <!-- JUDUL RAPOR -->
        <div class="text-center mb-6">
            <h3 class="text-lg font-bold underline uppercase">Laporan Hasil Evaluasi Mutaba'ah Al-Qur'an</h3>
        </div>

        <!-- IDENTITAS SANTRI -->
        <table class="w-full mb-6 text-sm font-semibold">
            <tr>
                <td class="w-32 py-1">Nama Santri</td>
                <td class="w-4">:</td>
                <td class="border-b border-dotted border-gray-400">{{ $santri->nama ?? $santri->nama_lengkap }}</td>
                
                <td class="w-28 pl-4 py-1">Tahun Ajaran</td>
                <td class="w-4">:</td>
                <!-- Placeholder Tahun Ajaran, bisa diganti dinamis dari database SIAK -->
                <td class="border-b border-dotted border-gray-400">2025/2026</td> 
            </tr>
            <tr>
                <td class="py-1">NIS / NISN</td>
                <td>:</td>
                <td class="border-b border-dotted border-gray-400">{{ $santri->nis }} / {{ $santri->nisn ?? '-' }}</td>
                
                <td class="pl-4 py-1">Semester</td>
                <td>:</td>
                <td class="border-b border-dotted border-gray-400">Ganjil</td>
            </tr>
            <tr>
                <td class="py-1">Kelas Asal</td>
                <td>:</td>
                <td class="border-b border-dotted border-gray-400">{{ $santri->santriKelas->first()->kelas->nama_kelas ?? 'Belum ada kelas' }}</td>
            </tr>
        </table>

        <!-- TABEL NILAI SESUAI REQUEST -->
        <table class="w-full border-collapse border border-black mb-8 text-center text-sm">
            <thead class="bg-gray-100 font-bold">
                <tr>
                    <th class="border border-black py-2 px-2 w-10">NO</th>
                    <th class="border border-black py-2 px-4 text-left">JENIS PENDIDIKAN AL-QUR'AN</th>
                    <th class="border border-black py-2 px-4 w-20">NILAI</th>
                    <th class="border border-black py-2 px-4 w-20">HURUF</th>
                    <th class="border border-black py-2 px-6">PREDIKAT</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Tilawah / Kelancaran -->
                <tr>
                    <td class="border border-black py-3">1</td>
                    <td class="border border-black py-3 text-left px-4 font-semibold">Tilawah (Kelancaran Hafalan/Bacaan)</td>
                    <td class="border border-black py-3 font-bold">{{ $avgTilawah }}</td>
                    <td class="border border-black py-3">{{ $kriteriaTilawah['huruf'] }}</td>
                    <td class="border border-black py-3 italic">{{ $kriteriaTilawah['predikat'] }}</td>
                </tr>
                <!-- 2. Tajwid -->
                <tr>
                    <td class="border border-black py-3">2</td>
                    <td class="border border-black py-3 text-left px-4 font-semibold">Tajwid & Fashahah</td>
                    <td class="border border-black py-3 font-bold">{{ $avgTajwid }}</td>
                    <td class="border border-black py-3">{{ $kriteriaTajwid['huruf'] }}</td>
                    <td class="border border-black py-3 italic">{{ $kriteriaTajwid['predikat'] }}</td>
                </tr>
                
                <!-- FOOTER TABEL: JUMLAH & RATA-RATA -->
                <tr class="bg-gray-50 font-bold">
                    <td colspan="2" class="border border-black py-2 text-right px-4">JUMLAH NILAI</td>
                    <td class="border border-black py-2 text-lg">{{ $jumlahNilai }}</td>
                    <td class="border border-black py-2 bg-gray-200" colspan="2"></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="2" class="border border-black py-2 text-right px-4">RATA-RATA AKHIR</td>
                    <td class="border border-black py-2 text-xl text-green-800">{{ $rataRataAkhir }}</td>
                    <td class="border border-black py-2 bg-gray-200" colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <!-- INFO CAPAIAN JUZ (Opsional tapi sangat bagus) -->
        <div class="mb-12 border border-black p-4 bg-gray-50 rounded">
            <p class="font-bold">Info Mutaba'ah:</p>
            <ul class="list-disc ml-5 mt-1">
                <li>Total Setoran Hafalan Semester Ini: <strong>{{ $santri->simaq_total_setoran ?? 0 }} kali</strong></li>
                <li>Pencapaian Juz Tertinggi: <strong>Juz {{ $santri->simaq_juz_tercapai ?? 0 }}</strong></li>
            </ul>
        </div>

        <!-- INFO CAPAIAN JUZ (Opsional tapi sangat bagus) -->
        <div class="mb-8 border border-black p-4 bg-gray-50 rounded">
            <p class="font-bold">Info Mutaba'ah:</p>
            <ul class="list-disc ml-5 mt-1">
                <li>Total Setoran Hafalan Semester Ini: <strong>{{ $santri->simaq_total_setoran ?? 0 }} kali</strong></li>
                <li>Pencapaian Juz Tertinggi: <strong>Juz {{ $santri->simaq_juz_tercapai ?? 0 }}</strong></li>
            </ul>
        </div>

        <!-- ================= TAMBAHKAN KODE INI ================= -->
        <!-- JUDUL TABEL RINCIAN -->
        <div class="text-center mb-4 mt-8 font-bold uppercase">
            <h4 class="text-md underline">Rincian Setoran Hafalan Al-Qur'an</h4>
            <p class="text-xs font-normal mt-1">Daftar hafalan ziyadah dan muraja'ah yang telah diujikan</p>
        </div>

        <!-- TABEL RINCIAN SETORAN HAFALAN -->
        <table class="w-full border-collapse border border-black mb-12 text-center text-sm">
            <thead class="bg-gray-100 font-bold">
                <tr>
                    <th class="border border-black py-2 px-2 w-10">NO</th>
                    <th class="border border-black py-2 px-4 w-28">TANGGAL</th>
                    <th class="border border-black py-2 px-4 text-left">HAFALAN SURAT / AYAT</th>
                    <th class="border border-black py-2 px-4 w-20">NILAI</th>
                    <th class="border border-black py-2 px-6 w-40">PREDIKAT</th>
                </tr>
            </thead>
            <tbody>
                <!-- Mengambil data nilai (khusus setoran harian) dan diurutkan dari yang paling lama ke terbaru -->
                @forelse($santri->simaqPenilaians->where('jenis', 'setoran_harian')->sortBy('tanggal') as $nilai)
                    <tr>
                        <td class="border border-black py-2">{{ $loop->iteration }}</td>
                        <td class="border border-black py-2">{{ \Carbon\Carbon::parse($nilai->tanggal)->format('d/m/Y') }}</td>
                        <td class="border border-black py-2 text-left px-4">{{ $nilai->surah_ayat }}</td>
                        <td class="border border-black py-2 font-bold">{{ floatval($nilai->nilai_akhir) }}</td>
                        <td class="border border-black py-2 italic">{{ $nilai->predikat ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="border border-black py-6 italic text-gray-500">
                            Santri belum memiliki catatan rincian setoran hafalan pada semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- ======================================================= -->

        <!-- TANDA TANGAN -->
        <table class="w-full text-center mt-12">
            <tr>
                <td class="w-1/2">
                    <p class="mb-20">Mengetahui,<br>Mudirul Ma'had</p>
                    <p class="font-bold underline uppercase">{{ $pondok['kepala'] ?? 'Mudir Pondok' }}</p>
                </td>
                <td class="w-1/2">
                    <p class="mb-20">Cirebon, {{ date('d F Y') }}<br>Guru Tahsin & Tahfizh</p>
                    <p class="font-bold underline uppercase">{{ auth()->user()->name }}</p>
                </td>
            </tr>
        </table>

    </div>

    <!-- Script Print Otomatis -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>