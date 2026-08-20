<?php

namespace App\Services;

use App\Models\Santri;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Nilai;
use App\Models\NilaiAkhir;
use App\Models\KomponenNilai;
use App\Models\PenugasanMengajar;
use App\Models\PredikatSikap;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PenilaianService
{
    /**
     * Hitung nilai akhir otomatis berdasarkan bobot komponen.
     * Formula: (avg_UH × bobot_UH%) + (avg_TUGAS × bobot%) + ... + (UAS × bobot%)
     */
    public function hitungNilaiAkhir(
        Santri $santri,
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): NilaiAkhir {
        $komponenList = KomponenNilai::where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $nilaiPerKomponen = [];
        $nilaiAkhirHitung = 0;

        foreach ($komponenList as $komponen) {
            $nilaiData = Nilai::where([
                'santri_id'          => $santri->id,
                'kelas_id'           => $kelas->id,
                'mata_pelajaran_id'  => $mataPelajaran->id,
                'komponen_nilai_id'  => $komponen->id,
                'tahun_ajaran_id'    => $tahunAjaran->id,
            ])->get();

            $rata = $nilaiData->isNotEmpty()
                ? round($nilaiData->avg('nilai'), 2)
                : null;

            $nilaiPerKomponen[$komponen->kode] = $rata;

            if ($rata !== null) {
                $nilaiAkhirHitung += ($rata * $komponen->bobot / 100);
            }
        }

        $nilaiAkhirHitung = round($nilaiAkhirHitung, 2);
        $predikat         = $this->getPredikat($nilaiAkhirHitung);
        // KKM per tingkatan, bukan lagi kolom mata_pelajaran.kkm global --
        // KKM ternyata beda per tingkatan untuk mapel yang sama (lihat
        // kkmUntukTingkatan() di model MataPelajaran).
        $tuntas           = $nilaiAkhirHitung >= $mataPelajaran->kkmUntukTingkatan($kelas->tingkatan_id);

        $record = NilaiAkhir::updateOrCreate(
            [
                'santri_id'         => $santri->id,
                'kelas_id'          => $kelas->id,
                'mata_pelajaran_id' => $mataPelajaran->id,
                'tahun_ajaran_id'   => $tahunAjaran->id,
            ],
            [
                'nilai_uh'      => $nilaiPerKomponen['UH']      ?? null,
                'nilai_tugas'   => $nilaiPerKomponen['TUGAS']   ?? null,
                'nilai_praktik' => $nilaiPerKomponen['PRAKTIK'] ?? null,
                'nilai_uts'     => $nilaiPerKomponen['UTS']     ?? null,
                'nilai_uas'     => $nilaiPerKomponen['UAS']     ?? null,
                'nilai_akhir'   => $nilaiAkhirHitung,
                'predikat'      => $predikat,
                'tuntas'        => $tuntas,
            ]
        );

        return $record;
    }

    /**
     * Hitung nilai akhir untuk seluruh santri dalam satu kelas & mapel.
     */
    public function hitungNilaiAkhirBulk(
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): array {
        $santriList = $kelas->santri;
        $results    = [];

        DB::transaction(function () use ($santriList, $kelas, $mataPelajaran, $tahunAjaran, &$results) {
            foreach ($santriList as $santri) {
                $results[] = $this->hitungNilaiAkhir($santri, $kelas, $mataPelajaran, $tahunAjaran);
            }
        });

        return $results;
    }

    /**
     * Ambil rekap nilai kelas untuk tampilan spreadsheet-style.
     * Return: ['santri' => [...], 'komponen' => [...], 'nilai' => [santri_id => [kode => nilai]]]
     */
    public function getRekapNilaiKelas(
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): array {
        $santriList = $kelas->santri()->with('user')->orderBy('nama_lengkap')->get();
        $komponen   = KomponenNilai::where('is_active', true)->orderBy('urutan')->get();

        $nilaiMap = Nilai::where([
            'kelas_id'          => $kelas->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'tahun_ajaran_id'   => $tahunAjaran->id,
        ])
            ->whereIn('santri_id', $santriList->pluck('id'))
            ->get()
            ->groupBy('santri_id');

        $nilaiAkhirMap = NilaiAkhir::where([
            'kelas_id'          => $kelas->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'tahun_ajaran_id'   => $tahunAjaran->id,
        ])
            ->whereIn('santri_id', $santriList->pluck('id'))
            ->get()
            ->keyBy('santri_id');

        $rows = [];
        foreach ($santriList as $santri) {
            $row = [
                'santri'      => $santri,
                'komponen'    => [],
                'nilai_akhir' => $nilaiAkhirMap[$santri->id] ?? null,
            ];

            foreach ($komponen as $k) {
                $nilaiSantri = $nilaiMap[$santri->id] ?? collect();
                $perKomponen = $nilaiSantri->where('komponen_nilai_id', $k->id);
                $row['komponen'][$k->kode] = $perKomponen->avg('nilai');
            }

            $rows[] = $row;
        }

        return [
            'santri'   => $santriList,
            'komponen' => $komponen,
            'rows'     => $rows,
            // KKM per tingkatan (lihat catatan sama di hitungNilaiAkhir()) --
            // ini yang ditampilkan sebagai ambang batas di rekap spreadsheet.
            'kkm'      => $mataPelajaran->kkmUntukTingkatan($kelas->tingkatan_id),
        ];
    }

    /**
     * Statistik nilai kelas (untuk dashboard guru / wakil kurikulum)
     */
    public function getStatistikKelas(
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): array {
        $nilaiAkhir = NilaiAkhir::where([
            'kelas_id'          => $kelas->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'tahun_ajaran_id'   => $tahunAjaran->id,
        ])->get();

        if ($nilaiAkhir->isEmpty()) {
            return [
                'rata_rata'      => 0,
                'tertinggi'      => 0,
                'terendah'       => 0,
                'jumlah_tuntas'  => 0,
                'jumlah_belum'   => 0,
                'persen_tuntas'  => 0,
                'distribusi'     => [],
            ];
        }

        $distribusi = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
        foreach ($nilaiAkhir as $n) {
            $p = $n->predikat ?? 'D';
            if (isset($distribusi[$p])) {
                $distribusi[$p]++;
            }
        }

        $jumlah     = $nilaiAkhir->count();
        $tuntas     = $nilaiAkhir->where('tuntas', true)->count();

        return [
            'rata_rata'     => round($nilaiAkhir->avg('nilai_akhir'), 2),
            'tertinggi'     => $nilaiAkhir->max('nilai_akhir'),
            'terendah'      => $nilaiAkhir->min('nilai_akhir'),
            'jumlah_tuntas' => $tuntas,
            'jumlah_belum'  => $jumlah - $tuntas,
            'persen_tuntas' => $jumlah > 0 ? round(($tuntas / $jumlah) * 100, 1) : 0,
            'distribusi'    => $distribusi,
        ];
    }

    /**
     * Konversi nilai angka ke predikat huruf (A-E), sesuai ambang batas
     * di config('siak.penilaian.predikat') -- supaya bisa diubah tanpa
     * sentuh kode kalau ambangnya perlu direvisi.
     */
    public function getPredikat(float $nilai): string
    {
        $tabel = config('siak.penilaian.predikat', []);

        foreach ($tabel as $tingkat) {
            if ($nilai >= $tingkat['min']) {
                return $tingkat['label'];
            }
        }

        return 'E';
    }

    /**
     * Konversi persentase kehadiran ke predikat huruf (A-E).
     * Sengaja memakai tabel ambang batas yang SAMA PERSIS dengan nilai
     * akademik (config('siak.penilaian.predikat')) -- dipakai untuk
     * auto-hitung Kedisiplinan wali kelas dari data presensi.
     */
    public function getPredikatKehadiran(float $persentase): string
    {
        return $this->getPredikat($persentase);
    }

    /**
     * Konversi nilai ekstrakurikuler (0-100) ke predikat kualitatif
     * (Sangat Baik/Baik/Cukup/Kurang), sesuai config('siak.ekstrakurikuler.predikat').
     */
    public function getPredikatEkstrakurikuler(float $nilai): string
    {
        $tabel = config('siak.ekstrakurikuler.predikat', []);

        foreach ($tabel as $tingkat) {
            if ($nilai >= $tingkat['min']) {
                return $tingkat['label'];
            }
        }

        return 'Kurang';
    }

    /**
     * Hitung persentase kehadiran santri untuk satu kelas & mapel
     */
    public function getPersentaseKehadiran(
        Santri $santri,
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): array {
        $pertemuanIds = \App\Models\Pertemuan::where([
            'kelas_id'          => $kelas->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
        ])
            ->whereBetween('tanggal', [
                $tahunAjaran->tanggal_mulai,
                $tahunAjaran->tanggal_selesai,
            ])
            ->pluck('id');

        $total  = $pertemuanIds->count();
        $hadir  = \App\Models\PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
            ->where('santri_id', $santri->id)
            ->where('status', 'hadir')
            ->count();

        return [
            'total'   => $total,
            'hadir'   => $hadir,
            'persen'  => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Hitung kehadiran GABUNGAN semua mata pelajaran untuk satu santri di satu
     * kelas & tahun ajaran (bukan per-mapel seperti getPersentaseKehadiran()).
     * Dipakai untuk:
     *  - Auto-hitung predikat Kedisiplinan (Wali Kelas)
     *  - Data "Kehadiran" gabungan di rapor (hadir/sakit/izin/alpa total)
     */
    public function getPersentaseKehadiranTotal(
        Santri $santri,
        Kelas $kelas,
        TahunAjaran $tahunAjaran
    ): array {
        $pertemuanIds = \App\Models\Pertemuan::where('kelas_id', $kelas->id)
            ->whereBetween('tanggal', [
                $tahunAjaran->tanggal_mulai,
                $tahunAjaran->tanggal_selesai,
            ])
            ->pluck('id');

        $presensi = \App\Models\PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
            ->where('santri_id', $santri->id)
            ->get();

        $total = $presensi->count();
        $hadir = $presensi->where('status', 'hadir')->count();
        $sakit = $presensi->where('status', 'sakit')->count();
        $izin  = $presensi->where('status', 'izin')->count();
        $alpa  = $presensi->where('status', 'alpa')->count();

        return [
            'total'  => $total,
            'hadir'  => $hadir,
            'sakit'  => $sakit,
            'izin'   => $izin,
            'alpa'   => $alpa,
            'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Peringkat santri dalam 1 kelas, berdasarkan TOTAL nilai akhir (jumlah
     * semua mapel) -- sesuai rumus RANK() di template rapor asli, bukan
     * rata-rata (urutannya sama saja selama semua santri dinilai di jumlah
     * mapel yang sama, tapi disamakan persis biar konsisten dengan template).
     *
     * Peringkat dihitung standar ala Excel RANK(): nilai sama dapat peringkat
     * sama, peringkat berikutnya "melompat" sesuai jumlah yang seri (bukan
     * peringkat rapat/dense).
     *
     * @return array [santri_id => ['jumlah' => float, 'peringkat' => int, 'peringkat_tampil' => int|null]]
     *   `peringkat_tampil` null kalau peringkat > 5 -- sesuai template asli,
     *   cuma 5 besar yang peringkatnya ditampilkan di rapor (selain itu "-").
     */
    /**
     * Peringkat santri dalam 1 kelas, berdasarkan TOTAL nilai akhir (jumlah
     * semua mapel) -- sesuai rumus RANK() di template rapor asli, bukan
     * rata-rata (urutannya sama saja selama semua santri dinilai di jumlah
     * mapel yang sama, tapi disamakan persis biar konsisten dengan template).
     *
     * Peringkat dihitung standar ala Excel RANK(): nilai sama dapat peringkat
     * sama, peringkat berikutnya "melompat" sesuai jumlah yang seri (bukan
     * peringkat rapat/dense).
     *
     * @return array [santri_id => ['jumlah' => float, 'peringkat' => int, 'peringkat_tampil' => int|null]]
     *   `peringkat_tampil` null kalau peringkat > 5 -- sesuai template asli,
     *   cuma 5 besar yang peringkatnya ditampilkan di rapor (selain itu "-").
     */
    public function getRankingKelas(Kelas $kelas, TahunAjaran $tahunAjaran): array
    {
        $santriList = $kelas->santri;

        $jumlahPerSantri = [];
        foreach ($santriList as $santri) {
            $jumlahPerSantri[$santri->id] = (float) NilaiAkhir::where('santri_id', $santri->id)
                ->where('kelas_id', $kelas->id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->sum('nilai_akhir');
        }

        $hasil = [];
        foreach ($jumlahPerSantri as $santriId => $jumlah) {
            $peringkat = collect($jumlahPerSantri)->filter(fn($v) => $v > $jumlah)->count() + 1;
            $hasil[$santriId] = [
                'jumlah'           => $jumlah,
                'peringkat'        => $peringkat,
                'peringkat_tampil' => $peringkat <= 5 ? $peringkat : null,
            ];
        }

        return $hasil;
    }

    /**
     * Rakit data leger nilai (Daftar Kumpulan Nilai Semester) 1 kelas penuh --
     * semua santri x semua mapel yang ditugaskan ke kelas ini, sekali tampil.
     * Beda dari getRekapNilaiKelas() (yang scope-nya 1 kelas x 1 mapel, dipakai
     * guru buat input nilai) -- ini scope-nya 1 kelas x SEMUA mapel, dipakai
     * wali kelas & kurikulum buat rekap akhir semester.
     *
     * Banyak bagian di sini SENGAJA reuse method lain di service ini
     * (getRankingKelas, getPersentaseKehadiranTotal) supaya angka Jumlah/
     * Peringkat/Ketidakhadiran konsisten dengan yang dipakai di rapor --
     * jangan hitung ulang dengan formula terpisah, nanti bisa beda hasil
     * antara leger dan rapor untuk kombinasi santri+mapel yang sama.
     *
     * Mapel yang jadi kolom = mapel yang ADA PENUGASAN MENGAJAR-nya ke kelas
     * ini di tahun ajaran ini (pola sama persis seperti RaporArabService::rakit()),
     * BUKAN "semua mata pelajaran aktif" -- supaya mapel yang memang tidak
     * diajarkan di kelas ini tidak ikut muncul kolomnya.
     *
     * @return array{mapelList: \Illuminate\Support\Collection, kkmPerMapel: \Illuminate\Support\Collection, rows: array}
     */
    public function getLegerKelas(Kelas $kelas, TahunAjaran $ta): array
    {
        $mapelIds = PenugasanMengajar::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->distinct()
            ->pluck('mata_pelajaran_id');

        $mapelList = MataPelajaran::whereIn('id', $mapelIds)
            ->orderBy('nama')
            ->get();

        $tingkatanId = $kelas->tingkatan_id;
        $kkmPerMapel = $mapelList->mapWithKeys(
            fn($m) => [$m->id => $m->kkmUntukTingkatan($tingkatanId)]
        );

        $santriList = $kelas->santri()->orderBy('nama_lengkap')->get();

        $nilaiAkhirMap = NilaiAkhir::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->whereIn('mata_pelajaran_id', $mapelIds)
            ->whereIn('santri_id', $santriList->pluck('id'))
            ->get()
            ->groupBy('santri_id');

        // Reuse method yang sama dengan yang dipakai RaporArabService, supaya
        // Jumlah & Peringkat di leger SELALU sinkron dengan yang di rapor.
        $ranking = $this->getRankingKelas($kelas, $ta);

        $predikatSikapMap = PredikatSikap::where('tahun_ajaran_id', $ta->id)
            ->whereIn('santri_id', $santriList->pluck('id'))
            ->get()
            ->keyBy('santri_id');

        $rows = [];
        foreach ($santriList as $santri) {
            $nilaiPerMapel = $nilaiAkhirMap[$santri->id] ?? collect();
            $nilaiByMapelId = $nilaiPerMapel->keyBy('mata_pelajaran_id');

            $nilaiTiapMapel = [];
            foreach ($mapelList as $mapel) {
                $nilaiTiapMapel[$mapel->id] = $nilaiByMapelId[$mapel->id]->nilai_akhir ?? null;
            }

            $jumlahMapelDinilai = $nilaiPerMapel->count();
            $dataRanking        = $ranking[$santri->id] ?? ['jumlah' => 0, 'peringkat' => null, 'peringkat_tampil' => null];
            $kehadiran           = $this->getPersentaseKehadiranTotal($santri, $kelas, $ta);
            $predikatSikap       = $predikatSikapMap[$santri->id] ?? null;

            $rows[] = [
                'santri'      => $santri,
                'nilai'       => $nilaiTiapMapel,
                'jumlah'      => $dataRanking['jumlah'],
                // Rata-rata dihitung dari mapel yang SUDAH ADA nilainya saja
                // (meniru semantik AVERAGE() Excel yang skip cell kosong),
                // bukan dibagi total kolom mapel -- kalau ada mapel yang
                // nilainya belum diisi sama sekali, tidak menurunkan rata-rata
                // secara tidak adil.
                'rata_rata'   => $jumlahMapelDinilai > 0
                    ? round($dataRanking['jumlah'] / $jumlahMapelDinilai, 1)
                    : 0,
                'peringkat'   => $dataRanking['peringkat_tampil'] ?? $dataRanking['peringkat'],
                'kehadiran'   => $kehadiran,
                'kepribadian' => [
                    'akhlaq'       => $this->predikatSikapKata($predikatSikap?->akhlak),
                    'kerajinan'    => $this->predikatSikapKata($predikatSikap?->kerajinan),
                    'kebersihan'   => $this->predikatSikapKata($predikatSikap?->kebersihan),
                    'kedisiplinan' => $this->predikatSikapKata($predikatSikap?->kedisiplinan),
                ],
            ];
        }

        return [
            'mapelList'   => $mapelList,
            'kkmPerMapel' => $kkmPerMapel,
            'rows'        => $rows,
        ];
    }

    /**
     * Kepribadian A/B/C -> tetap huruf, cuma normalisasi nilai kosong jadi '-'.
     * SENGAJA method terpisah dari RaporArabService::predikatSikapKata() yang
     * mengonversi ke kata Arab (ممتاز/جيد/مقبول) -- leger nilai formatnya latin,
     * jadi cukup huruf A/B/C polos seperti di contoh gambar leger.
     */
    private function predikatSikapKata(?string $huruf): string
    {
        return in_array($huruf, ['A', 'B', 'C'], true) ? $huruf : '-';
    }
}
