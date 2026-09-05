<?php
// ============================================================
// app/Services/SantriCreateService.php
// ============================================================
namespace App\Services;

use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;

/**
 * Service untuk fitur "Import Santri Baru" -- BERBEDA dari
 * SantriImportService (yang meng-UPDATE kelas/asrama santri yang SUDAH ADA,
 * dicocokkan by NISN). Service ini justru MEMBUAT santri baru dari nol,
 * sekaligus langsung menempatkannya ke kelas (rombel spesifik, mis. "7A")
 * untuk Tahun Ajaran aktif.
 *
 * Dipakai untuk migrasi data awal (mis. 300-1000 santri dari sistem/catatan
 * lama), BUKAN untuk pemakaian rutin sehari-hari -- input santri baru
 * satu-satu tetap lewat form Tambah Santri biasa.
 */
class SantriCreateService
{
    private TahunAjaran $tahunAjaran;

    public function __construct(?TahunAjaran $tahunAjaran = null)
    {
        $this->tahunAjaran = $tahunAjaran ?? TahunAjaran::aktif();
    }

    /**
     * @param array $rows - Array data dari Excel. Key per baris (setelah
     *   slug oleh WithHeadingRow): nisn, nama_lengkap, kelas, angkatan,
     *   jenis_kelamin.
     * @return array ['records' => [...], 'summary' => [...]]
     */
    public function validateAndPrepare(array $rows): array
    {
        $records = [];
        $summary = [
            'total' => 0,
            'valid' => 0,
            'errors' => 0,
        ];

        // Hitung dulu kemunculan tiap NISN DI DALAM FILE ITU SENDIRI -- buat
        // deteksi duplikat antar baris (bukan cuma duplikat ke database),
        // yang gampang kejadian kalau data 300-1000 baris digabung manual
        // dari beberapa sumber.
        $nisnCount = [];
        foreach ($rows as $row) {
            $n = trim($row['nisn'] ?? '');
            if ($n !== '') {
                $nisnCount[$n] = ($nisnCount[$n] ?? 0) + 1;
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $record = $this->processRow($row, $rowIndex + 2, $nisnCount); // +2 karena row 1 = header
            $records[] = $record;
            $summary['total']++;

            if (!empty($record['errors'])) {
                $summary['errors']++;
            } else {
                $summary['valid']++;
            }
        }

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    private function processRow(array $row, int $rowNumber, array $nisnCount): array
    {
        $nisn        = trim($row['nisn'] ?? '');
        $namaLengkap = trim($row['nama_lengkap'] ?? '');
        $kelasNama   = trim($row['kelas'] ?? '');
        $angkatan    = trim((string) ($row['angkatan'] ?? ''));
        $jkMentah    = trim((string) ($row['jenis_kelamin'] ?? ''));

        $record = [
            'row_number'           => $rowNumber,
            'nisn'                 => $nisn,
            'nama_lengkap'         => $namaLengkap,
            'kelas_nama'           => $kelasNama,
            'angkatan'             => $angkatan,
            'jenis_kelamin_mentah' => $jkMentah,
            'jenis_kelamin'        => null,
            'kelas_id'             => null,
            'errors'               => [],
        ];

        // Validasi NISN
        if (empty($nisn)) {
            $record['errors'][] = 'NISN tidak boleh kosong';
        } elseif (($nisnCount[$nisn] ?? 0) > 1) {
            $record['errors'][] = "NISN '{$nisn}' duplikat di file ini (muncul di lebih dari 1 baris)";
        } elseif (Santri::where('nisn', $nisn)->exists()) {
            $record['errors'][] = "NISN '{$nisn}' sudah terdaftar di database -- dilewati supaya tidak dobel";
        }

        // Validasi nama
        if (empty($namaLengkap)) {
            $record['errors'][] = 'Nama Lengkap tidak boleh kosong';
        }

        // Validasi angkatan
        if ($angkatan === '' || !ctype_digit($angkatan)) {
            $record['errors'][] = 'Angkatan harus diisi angka tahun (mis. 2026)';
        }

        // Validasi & normalisasi jenis kelamin -- JANGAN menebak kalau tidak
        // dikenali, lebih baik gagal jelas daripada salah assign gender santri.
        $jkNormalized = $this->normalisasiJenisKelamin($jkMentah);
        if (!$jkNormalized) {
            $record['errors'][] = "Jenis Kelamin '{$jkMentah}' tidak dikenali -- isi L/P atau Laki-laki/Perempuan";
        } else {
            $record['jenis_kelamin'] = $jkNormalized;
        }

        // Validasi kelas -- rombel SPESIFIK (mis. "7A"), dicari di TA aktif.
        // Sama seperti SantriImportService: kalau kelas belum dibuat duluan,
        // baris ini gagal (prasyarat, bukan bug).
        if (empty($kelasNama)) {
            $record['errors'][] = 'Kelas tidak boleh kosong';
        } else {
            $kelas = Kelas::where('nama', $kelasNama)
                ->when($this->tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $this->tahunAjaran->id))
                ->first();

            if (!$kelas) {
                $record['errors'][] = "Kelas '{$kelasNama}' tidak ditemukan di tahun ajaran " .
                    ($this->tahunAjaran?->nama_lengkap ?? 'aktif') .
                    " -- pastikan kelas ini sudah dibuat dulu di menu Kelas";
            } else {
                $record['kelas_id'] = $kelas->id;
            }
        }

        return $record;
    }

    /**
     * Normalisasi input jenis kelamin dari Excel ke 'L'/'P'. Menerima "L",
     * "P", atau kata penuh case-insensitive ("Laki-laki", "Perempuan", "Pr").
     * Return null kalau tidak dikenali sama sekali.
     */
    private function normalisasiJenisKelamin(string $nilai): ?string
    {
        $v = strtoupper(trim($nilai));

        if ($v === 'L' || str_starts_with($v, 'LAKI')) {
            return 'L';
        }

        if ($v === 'P' || str_starts_with($v, 'PEREMPUAN') || $v === 'PR') {
            return 'P';
        }

        return null;
    }

    /**
     * Buat santri baru dari baris yang disetujui + langsung tempatkan ke
     * kelas untuk TA aktif.
     *
     * @param array $approvals - ['row_index' => 'approve'|'skip', ...]
     * @param array $previewData - hasil dari validateAndPrepare()
     */
    public function save(array $approvals, array $previewData): array
    {
        $results = [
            'success' => 0,
            'skipped' => 0,
            'failed'  => 0,
            'messages' => [],
        ];

        foreach ($previewData['records'] as $index => $record) {
            $action = $approvals[$index] ?? 'skip';

            // Baris dengan error TIDAK BOLEH dipaksa "approve" -- beda dari
            // SantriImportService::save() (yang aman diloloskan karena cuma
            // UPDATE dan santri_id null otomatis skip), di sini approve pada
            // baris error bisa berarti bikin santri dengan kelas_id/jenis_
            // kelamin null -- jadi errors[] dicek eksplisit di sini.
            if ($action === 'skip' || !empty($record['errors'])) {
                $results['skipped']++;
                continue;
            }

            try {
                $santri = Santri::create([
                    'nisn'          => $record['nisn'],
                    'nama_lengkap'  => $record['nama_lengkap'],
                    'jenis_kelamin' => $record['jenis_kelamin'],
                    'angkatan'      => (int) $record['angkatan'],
                    'status'        => 'aktif',
                ]);

                SantriKelas::create([
                    'santri_id'       => $santri->id,
                    'kelas_id'        => $record['kelas_id'],
                    'tahun_ajaran_id' => $this->tahunAjaran->id,
                    'status'          => 'aktif',
                ]);

                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['messages'][] = "NISN {$record['nisn']}: Gagal dibuat - " . $e->getMessage();
            }
        }

        return $results;
    }
}
