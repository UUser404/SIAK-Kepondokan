<?php
// ============================================================
// app/Services/SantriImportService.php
// ============================================================
namespace App\Services;

use App\Models\Asrama;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;

class SantriImportService
{
    private TahunAjaran $tahunAjaran;

    public function __construct(?TahunAjaran $tahunAjaran = null)
    {
        $this->tahunAjaran = $tahunAjaran ?? TahunAjaran::aktif();
    }

    /**
     * Validasi dan prepare data untuk preview sebelum save.
     *
     * PENTING: record yang di-return cuma berisi ID (santri_id, kelas_id,
     * asrama_id), BUKAN objek Eloquent penuh. Hasil validateAndPrepare()
     * ini disimpan ke session di controller supaya bisa dipakai lagi saat
     * konfirmasi save() -- kalau isinya objek Eloquent lengkap (apalagi
     * setelah relasi ke-load), untuk 1000 baris payload session bisa
     * membengkak drastis. ID doang jauh lebih ringan; save() nanti query
     * ulang berdasarkan ID yang dibutuhkan saja.
     *
     * @param array $rows - Array data dari Excel. Maatwebsite\Excel dengan
     *   WithHeadingRow default nge-slug header ("Nama Lengkap" -> nama_lengkap,
     *   "Nama Arab" -> nama_arab, "NIS" -> nis, dst) -- jadi key di $row HARUS
     *   pakai bentuk slug ini, bukan nama kolom aslinya.
     * @return array Preview data dengan struktur: ['records' => [...], 'summary' => [...]]
     */
    public function validateAndPrepare(array $rows): array
    {
        $records = [];
        $summary = [
            'total' => 0,
            'valid' => 0,
            'warning' => 0,
            'errors' => 0,
        ];

        foreach ($rows as $rowIndex => $row) {
            $record = $this->processRow($row, $rowIndex + 2); // +2 karena row 1 adalah header
            $records[] = $record;
            $summary['total']++;

            if (!empty($record['errors'])) {
                $summary['errors']++;
            } elseif ($record['nama_berbeda'] || !$record['kelas_valid'] || !$record['asrama_valid']) {
                $summary['warning']++;
            } else {
                $summary['valid']++;
            }
        }

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    /**
     * Process satu row dari Excel.
     *
     * Key di $row pakai bentuk slug (default HeadingRowFormatter Maatwebsite\Excel):
     * "NIS" -> nis, "Nama Lengkap" -> nama_lengkap, "Nama Arab" -> nama_arab,
     * "Kelas" -> kelas, "Asrama" -> asrama.
     */
    private function processRow(array $row, int $rowNumber): array
    {
        $nis         = trim($row['nis'] ?? '');
        $namaLengkap = trim($row['nama_lengkap'] ?? '');
        $namaArab    = trim($row['nama_arab'] ?? '');
        $kelasBaru   = trim($row['kelas'] ?? '');
        $asramaBaru  = trim($row['asrama'] ?? '');

        $record = [
            'row_number' => $rowNumber,
            'nis' => $nis,
            'nama_baru' => $namaLengkap,
            'nama_arab_baru' => $namaArab,
            'kelas_baru' => $kelasBaru,
            'asrama_baru' => $asramaBaru,
            'santri_id' => null,
            'nama_lama' => '-',
            'nama_arab_lama' => '-',
            'nama_berbeda' => false,
            'nama_similarity' => 1.0,
            'kelas_lama' => '-',
            'kelas_valid' => true,
            'kelas_id' => null,
            'asrama_lama' => '-',
            'asrama_valid' => true,
            'asrama_id' => null,
            'errors' => [],
        ];

        // Validasi NIS
        if (empty($nis)) {
            $record['errors'][] = 'NIS tidak boleh kosong';
            return $record;
        }

        // Cari santri berdasarkan NIS
        $santri = Santri::where('nis', $nis)->first();
        if (!$santri) {
            $record['errors'][] = "Santri dengan NIS '{$nis}' tidak ditemukan";
            return $record;
        }

        $record['santri_id'] = $santri->id;
        $record['nama_lama'] = $santri->nama_lengkap;
        $record['nama_arab_lama'] = $santri->nama_arab ?? '-';

        // Check nama berbeda dengan similarity
        if (!empty($namaLengkap)) {
            $similarity = $this->calculateSimilarity($santri->nama_lengkap, $namaLengkap);
            $record['nama_similarity'] = $similarity;

            if ($similarity < 0.95) { // Jika < 95% similarity, flag sebagai berbeda
                $record['nama_berbeda'] = true;
            }
        }

        // Validasi kelas -- dicari di Tahun Ajaran TUJUAN (yang lagi dipilih admin
        // saat import, biasanya TA aktif). Kalau kelas belum dibuat dulu di TA itu
        // (mis. admin lupa bikin "8B" buat tahun ajaran baru), ini akan gagal --
        // itu prasyarat yang harus dipenuhi sebelum import kenaikan kelas.
        if (!empty($kelasBaru)) {
            $kelas = Kelas::where('nama', $kelasBaru)
                ->when($this->tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $this->tahunAjaran->id))
                ->first();

            if (!$kelas) {
                $record['errors'][] = "Kelas '{$kelasBaru}' tidak ditemukan di tahun ajaran " .
                    ($this->tahunAjaran?->nama_lengkap ?? 'aktif') .
                    " -- pastikan kelas ini sudah dibuat dulu di menu Kelas";
            } else {
                $record['kelas_id'] = $kelas->id;
                $kelasAktif = $santri->kelasAktif;
                $record['kelas_lama'] = $kelasAktif?->nama ?? '-';
                $record['kelas_valid'] = true;
            }
        }

        // Validasi asrama
        if (!empty($asramaBaru)) {
            $asrama = Asrama::where('nama', $asramaBaru)
                ->where('is_active', true)
                ->first();

            if (!$asrama) {
                $record['errors'][] = "Asrama '{$asramaBaru}' tidak ditemukan atau tidak aktif";
            } else {
                $record['asrama_id'] = $asrama->id;
                $penempatanAktif = $santri->penempatanKamar
                    ->where('is_aktif', true)
                    ->first();
                $record['asrama_lama'] = $penempatanAktif?->kamar?->asrama?->nama ?? '-';
                $record['asrama_valid'] = true;
            }
        }

        return $record;
    }

    /**
     * Hitung similarity antara 2 string menggunakan Levenshtein distance
     * Return value: 0 - 1 (1 = identical)
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === $str2) {
            return 1.0;
        }

        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);
        return 1.0 - ($distance / $maxLen);
    }

    /**
     * Simpan perubahan berdasarkan preview yang sudah dikonfirmasi.
     *
     * @param array $approvals - Array approval dari preview: ['row_index' => 'approve|reject|skip', ...]
     * @param array $previewData - Hasil dari validateAndPrepare()
     */
    public function save(array $approvals, array $previewData): array
    {
        $results = [
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
            'messages' => [],
        ];

        foreach ($previewData['records'] as $index => $record) {
            $action = $approvals[$index] ?? 'skip';

            if ($action === 'skip' || $action === 'reject' || !$record['santri_id']) {
                $results['skipped']++;
                continue;
            }

            // Aksi 'approve': update santri, kelas, dan asrama
            try {
                $santri = Santri::find($record['santri_id']);
                if (!$santri) {
                    $results['failed']++;
                    $results['messages'][] = "NIS {$record['nis']}: Santri tidak ditemukan lagi (mungkin sudah dihapus)";
                    continue;
                }

                // Update nama kalau ada (dan ada approval)
                if (!empty($record['nama_baru']) && $record['nama_berbeda']) {
                    $namaLama = $santri->nama_lengkap;
                    $santri->update(['nama_lengkap' => $record['nama_baru']]);
                    ActivityLogService::logUpdate($santri, ['nama_lengkap' => $namaLama]);
                }

                // Update Nama Arab kalau diisi -- tidak digerbang similarity check
                // seperti nama_lengkap (field ini sering kosong dari awal, jadi
                // "beda" dari kosong selalu wajar, bukan indikasi typo).
                if (!empty($record['nama_arab_baru'])) {
                    $santri->update(['nama_arab' => $record['nama_arab_baru']]);
                }

                // Update kelas kalau valid
                if ($record['kelas_id']) {
                    SantriKelas::updateOrCreate(
                        ['santri_id' => $santri->id, 'tahun_ajaran_id' => $this->tahunAjaran->id],
                        ['kelas_id' => $record['kelas_id'], 'status' => 'aktif']
                    );
                }

                // Update asrama kalau valid -- FIX: sebelumnya syaratnya salah cek
                // 'kelas_baru' (bug copy-paste dari blok kelas), seharusnya 'asrama_baru'.
                if ($record['asrama_id'] && !empty($record['asrama_baru'])) {
                    // Catatan: kamar spesifik tetap harus di-assign manual di form
                    // terpisah (Penempatan Kamar) -- di sini cuma menonaktifkan
                    // penempatan lama supaya santri "keluar" dari kamar/asrama lama.
                    $penempatanLama = $santri->penempatanKamar()
                        ->where('is_aktif', true)
                        ->first();

                    if ($penempatanLama) {
                        $penempatanLama->update(['is_aktif' => false]);
                    }

                    $results['messages'][] = "NIS {$santri->nis}: Asrama akan diubah ke {$record['asrama_baru']}, silakan assign kamar secara manual";
                }

                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['messages'][] = "NIS {$record['nis']}: Gagal update - " . $e->getMessage();
            }
        }

        return $results;
    }
}
