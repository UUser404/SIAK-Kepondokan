<?php

namespace App\Actions;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use App\Models\TenagaPendidik;
use App\Models\Kelas;
use App\Services\SimaqScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * SubmitSimaqPenilaianAction
 * 
 * Action untuk menyimpan penilaian SIMAQ dengan semua kalkulasi dan validasi.
 * Menggunakan transaction untuk memastikan data integrity.
 */
class SubmitSimaqPenilaianAction
{
    protected SimaqScoringService $scoringService;

    public function __construct(SimaqScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Execute action - simpan penilaian dengan kalkulasi lengkap
     * 
     * @param array $data data penilaian dari form
     * @return SimaqPenilaian model yang baru disimpan
     * @throws ValidationException jika validasi gagal
     * @throws \Exception jika terjadi error lain
     */
    public function execute(array $data): SimaqPenilaian
    {
        return DB::transaction(function () use ($data) {
            try {
                // Step 1: Validasi input
                $this->validateInput($data);

                // Step 2: Load models
                $santri = Santri::findOrFail($data['santri_id']);
                $guru   = TenagaPendidik::findOrFail($data['guru_id']);
                $kelas  = Kelas::findOrFail($data['kelas_id']);

                // Step 3: Prepare data untuk kalkulasi
                $komponenData = [
                    'jenis'                    => $data['jenis'],
                    'kesalahan_kelancaran'     => $data['kesalahan_kelancaran'] ?? 0,
                    'kesalahan_tajwid'         => $data['kesalahan_tajwid'] ?? 0,
                    'kesalahan_makhraj'        => $data['kesalahan_makhraj'] ?? 0,
                ];

                // Jika ujian, tambahkan nilai pemantapan & tasmi
                if (in_array($data['jenis'], ['tasmi', 'pemantapan'])) {
                    $komponenData['nilai_pemantapan'] = $data['nilai_pemantapan'] ?? null;
                    $komponenData['nilai_tasmi']      = $data['nilai_tasmi'] ?? null;
                }

                // Step 4: Kalkulasi nilai menggunakan scoring service
                $nilaiKalkulasi = $this->scoringService->calculatePenilaian($komponenData);

                // Step 5: Prepare data untuk insert
                $penilaianData = [
                    'santri_id'             => $santri->id,
                    'guru_id'               => $guru->id,
                    'kelas_id'              => $kelas->id,
                    'program'               => $data['program'],
                    'jenis'                 => $data['jenis'],
                    'tanggal'               => $data['tanggal'],
                    'surah_ayat'            => $data['surah_ayat'] ?? null,
                    'halaman'               => $data['halaman'] ?? null,
                    'juz'                   => $data['juz'] ?? null,
                    'kesalahan_kelancaran'  => $data['kesalahan_kelancaran'] ?? 0,
                    'kesalahan_tajwid'      => $data['kesalahan_tajwid'] ?? 0,
                    'kesalahan_makhraj'     => $data['kesalahan_makhraj'] ?? 0,
                    // Nilai kalkulasi dari service
                    'nilai_kelancaran'      => $nilaiKalkulasi['nilai_kelancaran'],
                    'nilai_tajwid'          => $nilaiKalkulasi['nilai_tajwid'],
                    'nilai_makhraj'         => $nilaiKalkulasi['nilai_makhraj'],
                    'nilai_akhir'           => $nilaiKalkulasi['nilai_akhir'],
                    'bintang'               => $nilaiKalkulasi['bintang'],
                    'huruf'                 => $nilaiKalkulasi['huruf'],
                    'predikat'              => $nilaiKalkulasi['predikat'],
                    'status_kelulusan'      => $nilaiKalkulasi['status_kelulusan'],
                    'catatan'               => $data['catatan'] ?? null,
                ];

                // Step 6: Create atau update penilaian
                // Check duplikat: hanya boleh 1 penilaian per santri/guru/jenis/tanggal
                $penilaian = SimaqPenilaian::updateOrCreate(
                    [
                        'santri_id'  => $santri->id,
                        'guru_id'    => $guru->id,
                        'jenis'      => $data['jenis'],
                        'tanggal'    => $data['tanggal'],
                    ],
                    $penilaianData
                );

                Log::info('SubmitSimaqPenilaianAction: Penilaian berhasil disimpan', [
                    'penilaian_id' => $penilaian->id,
                    'santri_id'    => $santri->id,
                    'guru_id'      => $guru->id,
                    'nilai_akhir'  => $penilaian->nilai_akhir,
                ]);

                return $penilaian;
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::warning('SubmitSimaqPenilaianAction: Validasi gagal', [
                    'errors' => $e->errors(),
                ]);
                throw $e;
            } catch (\Throwable $e) {
                Log::error('SubmitSimaqPenilaianAction: Error', [
                    'data'  => $data,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Validasi input data penilaian
     * 
     * @throws ValidationException
     */
    private function validateInput(array $data): void
    {
        $errors = [];

        // Validasi required fields
        $requiredFields = [
            'santri_id'             => 'Santri harus dipilih',
            'guru_id'               => 'Guru harus dipilih',
            'kelas_id'              => 'Kelas harus dipilih',
            'program'               => 'Program harus dipilih',
            'jenis'                 => 'Jenis penilaian harus dipilih',
            'tanggal'               => 'Tanggal penilaian harus dipilih',
        ];

        foreach ($requiredFields as $field => $message) {
            if (empty($data[$field])) {
                $errors[$field] = [$message];
            }
        }

        // Validasi enum values
        $validPrograms = ['hafalan', 'tilawah', 'tahsin'];
        if (!empty($data['program']) && !in_array($data['program'], $validPrograms)) {
            $errors['program'] = ['Program invalid'];
        }

        $validJenis = ['setoran_harian', 'tasmi', 'pemantapan'];
        if (!empty($data['jenis']) && !in_array($data['jenis'], $validJenis)) {
            $errors['jenis'] = ['Jenis penilaian invalid'];
        }

        // Validasi kesalahan (integer non-negatif)
        $errorFields = ['kesalahan_kelancaran', 'kesalahan_tajwid', 'kesalahan_makhraj'];
        foreach ($errorFields as $field) {
            if (isset($data[$field]) && ($data[$field] < 0 || !is_numeric($data[$field]))) {
                $errors[$field] = ["$field harus non-negatif"];
            }
        }

        // Validasi untuk ujian: nilai pemantapan & tasmi harus ada
        if (in_array($data['jenis'] ?? null, ['tasmi', 'pemantapan'])) {
            if (empty($data['nilai_pemantapan']) && empty($data['nilai_tasmi'])) {
                $errors['nilai_ujian'] = ['Minimal salah satu dari pemantapan atau tasmi harus diisi'];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
