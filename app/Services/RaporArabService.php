<?php
// ============================================================
// app/Services/RaporArabService.php
// ============================================================
namespace App\Services;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\PenugasanMengajar;
use App\Models\PredikatSikap;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\Ekstrakurikuler;
use App\Models\NilaiEkstrakurikuler;

class RaporArabService
{
    public function __construct(
        private PenilaianService $penilaianService,
        private TerbilangArabService $terbilang,
    ) {}

    public function rakit(Santri $santri, Kelas $kelas, TahunAjaran $ta): array
    {
        $tingkatanId = $kelas->tingkatan_id;

        // Mapel yang benar ditugaskan untuk kelas ini
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

        $baris = collect($baris)->groupBy('kategori');

        $ranking = $this->penilaianService->getRankingKelas($kelas, $ta);
        $dataRanking = $ranking[$santri->id] ?? ['jumlah' => 0, 'peringkat_tampil' => null];
        $jumlahMapel = $mapelList->count();
        $rataRata = $jumlahMapel > 0 ? round($dataRanking['jumlah'] / $jumlahMapel, 1) : 0;

        $predikatSikap = PredikatSikap::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->first();

        $kehadiranAuto = $this->penilaianService->getPersentaseKehadiranTotal($santri, $kelas, $ta);

        // ============================================================
        // 🔥 DATA TAMBAHAN UNTUK FORMAT BARU
        // ============================================================

        // 1. Ekstrakurikuler
        $ekstrakurikuler = NilaiEkstrakurikuler::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->with('ekstrakurikuler')
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->ekstrakurikuler->nama ?? '-',
                    'keterangan' => $item->keterangan ?? 'مشاركة جيدة',
                ];
            })
            ->toArray();

        // 2. Catatan Wali Kelas (dari PredikatSikap atau field tersendiri)
        $catatanWali = $predikatSikap?->catatan_wali ?? '-';

        // 3. Fase berdasarkan tingkatan
        $fase = $this->getFase($kelas);

        // 4. Keterangan Kenaikan Kelas
        $keteranganKenaikan = $this->getKeteranganKenaikan($rataRata);

        // 5. Kokurikuler (hardcode dulu atau ambil dari model)
        $kokurikulerText = $this->getKokurikulerText($santri, $ta);

        // 6. NIP
        $waliKelasNip = $kelas->waliKelas?->nip ?? '-';
        $kepalaSekolahNip = $ta->nip_kepala_sekolah ?? '-';

        return [
            'santri' => [
                'nama_latin' => $santri->nama_lengkap,
                'nama_arab'  => $santri->nama_arab,
                'nis'        => $santri->nis,
                'nisn'       => $santri->nisn ?? '-',
                'alamat'     => $santri->alamat ?? '-',
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
            'wali_kelas' => $kelas->waliKelas?->nama_arab ?? $kelas->waliKelas?->name ?? '-',
            'wali_kelas_nip' => $waliKelasNip,
            'kepala_sekolah' => $ta->nama_kepala_sekolah_arab ?? $ta->nama_kepala_sekolah ?? '-',
            'kepala_sekolah_nip' => $kepalaSekolahNip,
            'mudir' => $ta->nama_mudir_arab ?? '-',
            'tanggal_masehi' => $ta->tanggal_selesai,
            'tanggal_hijriah' => $ta->tanggal_rapor_hijriah,

            // ============================================================
            // 🔥 DATA BARU UNTUK FORMAT RAPOR
            // ============================================================
            'sekolah_nama' => 'معهد الإسلام الإسلامي للتربية الإسلامية الحديثة',
            'fase' => $fase,
            'kokurikuler_text' => $kokurikulerText,
            'ekstrakurikuler' => $ekstrakurikuler,
            'catatan_wali_kelas' => $catatanWali,
            'keterangan_kenaikan' => $keteranganKenaikan,
            'tanggapan_ortu' => '', // Untuk diisi oleh orang tua nanti
            'tempat' => 'ثيرون',
        ];
    }

    /**
     * Mendapatkan fase berdasarkan tingkatan
     */
    private function getFase(Kelas $kelas): string
    {
        $tingkatan = $kelas->tingkatan->nama ?? '';

        $map = [
            'VII' => 'D',
            'VIII' => 'D',
            'IX' => 'D',
            'X' => 'E',
            'XI' => 'F',
            'XII' => 'F',
        ];

        foreach ($map as $key => $fase) {
            if (str_contains($tingkatan, $key)) {
                return $fase;
            }
        }

        return '-';
    }

    /**
     * Mendapatkan keterangan kenaikan kelas
     */
    private function getKeteranganKenaikan(float $rataRata): string
    {
        if ($rataRata >= 70) {
            return 'ينتقل إلى الفصل التالي';
        } elseif ($rataRata >= 60) {
            return 'ينتقل إلى الفصل التالي مع تحسين الأداء';
        }
        return 'يبقى في نفس الفصل للتحسين';
    }

    /**
     * Mendapatkan teks kokurikuler
     */
    private function getKokurikulerText(Santri $santri, TahunAjaran $ta): string
    {
        // TODO: Ambil dari model Kokurikuler jika sudah ada
        // Sementara menggunakan teks default
        return 'الطالب يشارك في الأنشطة المدرسية بنشاط ويظهر روح التعاون مع الزملاء';
    }

    /**
     * Predikat per mapel
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
     * Deskripsi naratif
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
     * Kepribadian A/B/C -> kata Arab
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
