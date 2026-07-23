<?php

namespace App\Services;

use App\Models\SimaqPenilaian;
use Illuminate\Support\Facades\Log;

/**
 * SimaqScoringService
 * 
 * Engine kalkulasi nilai untuk modul SIMAQ.
 * Menangani semua perhitungan dengan error handling yang ketat.
 */
class SimaqScoringService
{
    /**
     * Hitung nilai kelancaran
     * Formula: 100 - kesalahan (minimum 0)
     */
    public function hitungKelancaran(int $kesalahan = 0): float
    {
        $nilai = 100 - $kesalahan;
        // Jamin range [0, 100]
        return max(0, min(100, (float)$nilai));
    }

    /**
     * Hitung nilai tajwid atau makhraj (tier-based)
     * - 0 salah = 100
     * - 1-5 salah = 99
     * - 6-15 salah = 97
     * - >=16 salah = 95
     */
    public function hitungTajwidMakhraj(int $kesalahan = 0): float
    {
        return (float)match (true) {
            $kesalahan === 0 => 100,
            $kesalahan <= 5  => 99,
            $kesalahan <= 15 => 97,
            default          => 95,
        };
    }

    /**
     * Hitung nilai per sesi (PENILAIAN HARIAN)
     * Formula: (Kelancaran + Tajwid + Makharijul Huruf) / 3
     * 
     * @throws \InvalidArgumentException jika ada nilai yang invalid
     */
    public function hitungNilaiSesi(
        float $kelancaran,
        float $tajwid,
        float $makhraj
    ): float {
        try {
            // Validasi semua parameter
            $kelancaran = $this->validateNilai($kelancaran);
            $tajwid     = $this->validateNilai($tajwid);
            $makhraj    = $this->validateNilai($makhraj);

            // Hitung rata-rata
            $rata = ($kelancaran + $tajwid + $makhraj) / 3;

            // Bulatkan ke 2 desimal
            return round($rata, 2);
        } catch (\Exception $e) {
            Log::error('SimaqScoringService: Error hitungNilaiSesi', [
                'kelancaran' => $kelancaran,
                'tajwid'     => $tajwid,
                'makhraj'    => $makhraj,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Hitung nilai imtihan (PENILAIAN UJIAN)
     * Formula: (Pemantapan + Tasmi) / 2
     * 
     * EDGE CASE HANDLING:
     * - Jika kedua ada: gunakan rata
     * - Jika hanya satu: gunakan yang ada (dengan warning log)
     * - Jika tidak ada satupun: throw exception
     * 
     * @throws \Exception jika kedua komponen NULL
     */
    public function hitungNilaiImtihan(
        ?float $pemantapan = null,
        ?float $tasmi = null
    ): float {
        try {
            // Jika kedua ada: ambil rata
            if ($pemantapan !== null && $tasmi !== null) {
                $pemantapan = $this->validateNilai($pemantapan);
                $tasmi      = $this->validateNilai($tasmi);
                return round(($pemantapan + $tasmi) / 2, 2);
            }

            // Jika hanya satu ada: gunakan yang ada + log warning
            if ($pemantapan !== null) {
                Log::warning('SimaqScoringService: Tasmi missing, using pemantapan only', [
                    'pemantapan' => $pemantapan,
                ]);
                return round($this->validateNilai($pemantapan), 2);
            }

            if ($tasmi !== null) {
                Log::warning('SimaqScoringService: Pemantapan missing, using tasmi only', [
                    'tasmi' => $tasmi,
                ]);
                return round($this->validateNilai($tasmi), 2);
            }

            // Jika tidak ada satupun: ERROR
            throw new \Exception(
                'Pemantapan dan Tasmi tidak boleh keduanya NULL pada ujian'
            );
        } catch (\Exception $e) {
            Log::error('SimaqScoringService: Error hitungNilaiImtihan', [
                'pemantapan' => $pemantapan,
                'tasmi'      => $tasmi,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validasi dan cast nilai ke float dengan range check
     * 
     * @throws \InvalidArgumentException jika tipe data salah atau range invalid
     */
    private function validateNilai($nilai): float
    {
        if (!is_numeric($nilai)) {
            throw new \InvalidArgumentException(
                "Nilai harus numeric, got: " . gettype($nilai)
            );
        }

        $float = (float)$nilai;
        if ($float < 0 || $float > 100) {
            throw new \InvalidArgumentException(
                "Nilai harus dalam range [0, 100], got: $float"
            );
        }

        return $float;
    }

    /**
     * Mapping nilai numerikal ke huruf predikat
     * Konsisten untuk semua jenis penilaian
     */
    private function mapHurufPredikat(float $nilai): array
    {
        return match (true) {
            $nilai >= 95 => [
                'huruf'    => 'A+',
                'predikat' => "Mumtaz Murtafi'",
            ],
            $nilai >= 90 => [
                'huruf'    => 'A',
                'predikat' => 'Mumtaz',
            ],
            $nilai >= 85 => [
                'huruf'    => 'B+',
                'predikat' => "Jayyid Jiddan Murtafi'",
            ],
            $nilai >= 80 => [
                'huruf'    => 'B',
                'predikat' => 'Jayyid Jiddan',
            ],
            $nilai >= 75 => [
                'huruf'    => 'C+',
                'predikat' => "Jayyid Murtafi'",
            ],
            $nilai >= 70 => [
                'huruf'    => 'C',
                'predikat' => 'Jayyid',
            ],
            default => [
                'huruf'    => 'D',
                'predikat' => 'Maqbul',
            ],
        };
    }

    /**
     * Hitung bintang dari nilai (tier-based)
     */
    public function hitungBintang(float $nilai): int
    {
        $nilai = $this->validateNilai($nilai);

        return match (true) {
            $nilai >= 95 => 5,
            $nilai >= 85 => 4,
            $nilai >= 75 => 3,
            $nilai >= 65 => 2,
            default      => 1,
        };
    }

    /**
     * Hitung badge pencapaian dari total setoran dan bintang
     */
    public function hitungBadge(int $totalSetoran, int $bintang): ?string
    {
        if ($totalSetoran >= 100 && $bintang >= 4) {
            return 'Hafiz Muda';
        }

        if ($totalSetoran >= 30) {
            return 'Penghafal Awal';
        }

        return null;
    }

    /**
     * Get kriteria nilai dengan conditional logic berdasarkan jenis penilaian
     * 
     * PENTING: 
     * - Setoran Harian: Return hanya [huruf, predikat], NO status_kelulusan
     * - Ujian (tasmi/pemantapan): Apply KKM 90, return [huruf, predikat, status_kelulusan]
     * 
     * @return array dengan keys: huruf, predikat, status_kelulusan (nullable)
     */
    public function getKriteriaNilai(float $nilai, string $jenisPenilaian): array
    {
        try {
            $nilai = $this->validateNilai($nilai);

            // Step 1: Map huruf & predikat (konsisten untuk semua jenis)
            $kriteria = $this->mapHurufPredikat($nilai);

            // Step 2: Tentukan status kelulusan based on jenis
            if ($jenisPenilaian === 'setoran_harian') {
                // Penilaian harian: tidak ada status kelulusan
                // Hanya return huruf & predikat
                return $kriteria;
            }

            if (in_array($jenisPenilaian, ['tasmi', 'pemantapan'])) {
                // Ujian/Imtihan: Apply KKM = 90
                $kkm = 90;
                
                if ($nilai >= 95) {
                    $kriteria['status_kelulusan'] = 'Lulus (Mutqin)';
                } elseif ($nilai >= $kkm) {
                    $kriteria['status_kelulusan'] = 'Lulus';
                } else {
                    $kriteria['status_kelulusan'] = 'Tidak Lulus';
                }
                
                return $kriteria;
            }

            // Edge case: jenis tidak dikenal
            Log::warning('SimaqScoringService: Unknown jenisPenilaian', [
                'jenisPenilaian' => $jenisPenilaian,
                'nilai'          => $nilai,
            ]);

            // Return tanpa status kelulusan jika jenis tidak dikenal
            return $kriteria;
        } catch (\Exception $e) {
            Log::error('SimaqScoringService: Error getKriteriaNilai', [
                'nilai'              => $nilai,
                'jenisPenilaian'     => $jenisPenilaian,
                'error'              => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate complete penilaian dengan semua komponen
     * Ini adalah main method yang mengorkestra seluruh flow kalkulasi
     * 
     * @param array $data komponen kesalahan dan jenis penilaian
     * @return array hasil kalkulasi lengkap
     */
    public function calculatePenilaian(array $data): array
    {
        try {
            $jenis = $data['jenis'] ?? null;

            // Validasi jenis penilaian
            if (!in_array($jenis, ['setoran_harian', 'tasmi', 'pemantapan'])) {
                throw new \InvalidArgumentException("Jenis penilaian invalid: $jenis");
            }

            $hasil = [
                'nilai_kelancaran' => null,
                'nilai_tajwid'     => null,
                'nilai_makhraj'    => null,
                'nilai_akhir'      => null,
                'bintang'          => null,
                'huruf'            => null,
                'predikat'         => null,
                'status_kelulusan' => null,
            ];

            if ($jenis === 'setoran_harian') {
                // Hitung komponen per sesi
                $hasil['nilai_kelancaran'] = $this->hitungKelancaran($data['kesalahan_kelancaran'] ?? 0);
                $hasil['nilai_tajwid']     = $this->hitungTajwidMakhraj($data['kesalahan_tajwid'] ?? 0);
                $hasil['nilai_makhraj']    = $this->hitungTajwidMakhraj($data['kesalahan_makhraj'] ?? 0);

                // Hitung nilai akhir dari rata 3 komponen
                $hasil['nilai_akhir'] = $this->hitungNilaiSesi(
                    $hasil['nilai_kelancaran'],
                    $hasil['nilai_tajwid'],
                    $hasil['nilai_makhraj']
                );

                // Hitung bintang
                $hasil['bintang'] = $this->hitungBintang($hasil['nilai_akhir']);

                // Get kriteria (huruf & predikat, NO status_kelulusan)
                $kriteria = $this->getKriteriaNilai($hasil['nilai_akhir'], $jenis);
                $hasil['huruf']    = $kriteria['huruf'];
                $hasil['predikat'] = $kriteria['predikat'];
                // status_kelulusan tetap null untuk setoran_harian
            } elseif (in_array($jenis, ['tasmi', 'pemantapan'])) {
                // Ujian: gunakan nilai pemantapan & tasmi
                $hasil['nilai_akhir'] = $this->hitungNilaiImtihan(
                    $data['nilai_pemantapan'] ?? null,
                    $data['nilai_tasmi'] ?? null
                );

                // Hitung bintang
                $hasil['bintang'] = $this->hitungBintang($hasil['nilai_akhir']);

                // Get kriteria dengan status kelulusan
                $kriteria = $this->getKriteriaNilai($hasil['nilai_akhir'], $jenis);
                $hasil['huruf']            = $kriteria['huruf'];
                $hasil['predikat']         = $kriteria['predikat'];
                $hasil['status_kelulusan'] = $kriteria['status_kelulusan'] ?? null;
            }

            return $hasil;
        } catch (\Exception $e) {
            Log::error('SimaqScoringService: Error calculatePenilaian', [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
