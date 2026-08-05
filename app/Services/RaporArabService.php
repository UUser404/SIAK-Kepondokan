<?php
// ============================================================
// app/Services/RaporArabService.php
// Merakit semua data untuk 1 rapor santri (2 halaman), sesuai rumus
// PERSIS dari sheet "RAPORT" pada file template asli yang diberikan
// pengguna (RAPORT_7A_GANJIL_2025_2026.xlsx). Jangan ubah logic
// predikat/kesimpulan di sini tanpa cek ulang ke file itu.
// ============================================================
namespace App\Services;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\PenugasanMengajar;
use App\Models\PredikatSikap;
use App\Models\Santri;
use App\Models\TahunAjaran;

class RaporArabService
{
    public function __construct(
        private PenilaianService $penilaianService,
        private TerbilangArabService $terbilang,
    ) {}

    public function rakit(Santri $santri, Kelas $kelas, TahunAjaran $ta): array
    {
        $tingkatanId = $kelas->tingkatan_id;

        // Mapel yang benar ditugaskan (Penugasan Mengajar) untuk kelas ini --
        // konsisten dengan sumber "mapel per kelas" yang sudah dipakai di
        // dashboard Kurikulum. Mapel tanpa `kategori` diisi TIDAK ikut tampil
        // di rapor (keputusan desain -- kategori wajib buat pengelompokan).
        $mapelIds = PenugasanMengajar::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->distinct()
            ->pluck('mata_pelajaran_id');

        $mapelList = MataPelajaran::whereIn('id', $mapelIds)
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();

        $nilaiPerMapel = NilaiAkhir::where('santri_id', $santri->id)
            ->where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->whereIn('mata_pelajaran_id', $mapelIds)
            ->get()
            ->keyBy('mata_pelajaran_id');

        $baris = [];
        foreach ($mapelList as $mapel) {
            $nilaiAkhir = $nilaiPerMapel->get($mapel->id)?->nilai_akhir;
            $kkm        = $mapel->kkmUntukTingkatan($tingkatanId);

            $baris[] = [
                'kategori'     => $mapel->kategori,
                'nama'         => $mapel->nama,
                'kkm'          => $kkm,
                'nilai_angka'  => $nilaiAkhir,
                'nilai_kata'   => $nilaiAkhir !== null ? $this->terbilang->kataArab($nilaiAkhir) : '-',
                'predikat'     => $this->predikat($nilaiAkhir, $kkm),
                'deskripsi'    => $this->deskripsi($nilaiAkhir, $kkm),
            ];
        }

        // Kelompokkan per kategori (urutan tampil di rapor: per kategori,
        // masing-masing baris mapelnya di bawahnya -- sesuai template asli).
        $baris = collect($baris)->groupBy('kategori');

        // Ranking & jumlah -- dari PenilaianService, rumus sama persis
        // RANK() di template asli (lihat catatan di method itu).
        $ranking = $this->penilaianService->getRankingKelas($kelas, $ta);
        $dataRanking = $ranking[$santri->id] ?? ['jumlah' => 0, 'peringkat_tampil' => null];
        $jumlahMapel = $mapelList->count();
        $rataRata = $jumlahMapel > 0 ? round($dataRanking['jumlah'] / $jumlahMapel, 1) : 0;

        // Kepribadian + ketidakhadiran (dari PredikatSikap, default auto-hitung
        // presensi kalau belum di-override manual wali kelas).
        $predikatSikap = PredikatSikap::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->first();

        $kehadiranAuto = $this->penilaianService->getPersentaseKehadiranTotal($santri, $kelas, $ta);

        return [
            'santri' => [
                'nama_latin' => $santri->nama_lengkap,
                'nama_arab'  => $santri->nama_arab,
                'nis'        => $santri->nis,
            ],
            'kelas' => $kelas,
            'ta'    => $ta,
            'mapel_per_kategori' => $baris,
            'jumlah'    => $dataRanking['jumlah'],
            'rata_rata' => $rataRata,
            'peringkat_tampil' => $dataRanking['peringkat_tampil'],
            'jumlah_santri_kelas' => $kelas->santri()->count(),
            'kepribadian' => [
                'akhlaq'       => $this->predikatSikapKata($predikatSikap?->akhlak),
                'kerajinan'    => $this->predikatSikapKata($predikatSikap?->kerajinan),
                'kebersihan'   => $this->predikatSikapKata($predikatSikap?->kebersihan),
                'kedisiplinan' => $this->predikatSikapKata($predikatSikap?->kedisiplinan),
            ],
            'ketidakhadiran' => [
                'sakit' => $predikatSikap?->sakit_override ?? $kehadiranAuto['sakit'],
                'izin'  => $predikatSikap?->izin_override ?? $kehadiranAuto['izin'],
                'alpa'  => $predikatSikap?->alpa_override ?? $kehadiranAuto['alpa'],
            ],
            'kesimpulan' => $rataRata >= 60 ? 'ناجح' : 'راسب',
            'wali_kelas' => $kelas->waliKelas?->nama_arab,
            'kepala_sekolah' => $ta->nama_kepala_sekolah_arab,
            'mudir' => $ta->nama_mudir_arab,
            'tanggal_masehi' => $ta->tanggal_selesai,
            'tanggal_hijriah' => $ta->tanggal_rapor_hijriah,
        ];
    }

    /**
     * Predikat per mapel -- rumus PERSIS dari sel B10:B22 di sheet RAPORT
     * template asli:
     * IF(nilai>=90,"ممتاز", IF(nilai>=kkm+10,"جيد جدا", IF(nilai>kkm,"جيد",
     * IF(nilai=kkm,"مقبول", IF(nilai=kkm-0.1,"مقبول بالتحسين",
     * IF(nilai>=1,"راسب","-"))))))
     */
    private function predikat(?float $nilai, ?int $kkm): string
    {
        if ($nilai === null || $kkm === null) {
            return '-';
        }
        if ($nilai >= 90) return 'ممتاز';
        if ($nilai >= $kkm + 10) return 'جيد جدا';
        if ($nilai > $kkm) return 'جيد';
        if ($nilai == $kkm) return 'مقبول';
        if ($nilai == $kkm - 0.1) return 'مقبول بالتحسين';
        if ($nilai >= 1) return 'راسب';

        return '-';
    }

    /**
     * Deskripsi naratif halaman 2 -- rumus PERSIS dari sel A42:A54 template
     * asli, cuma bungkus predikat() di atas jadi 1 kalimat.
     */
    private function deskripsi(?float $nilai, ?int $kkm): string
    {
        $p = $this->predikat($nilai, $kkm);
        if ($p === '-') {
            return '-';
        }

        return "النتيجة التى حصل/ت عليها الطالب/ـة مكتملة بتقدير {$p}";
    }

    /**
     * Kepribadian A/B/C -> kata Arab -- rumus PERSIS dari sel E56:E59
     * template asli (C->مقبول, B->جيد, A->ممتاز). Catatan: file asli
     * salah ketik "مقيبول" untuk C, di sini ditulis benar "مقبول".
     */
    private function predikatSikapKata(?string $huruf): string
    {
        return match ($huruf) {
            'A' => 'ممتاز',
            'B' => 'جيد',
            'C' => 'مقبول',
            default => '-',
        };
    }
}
