<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use Illuminate\Console\Command;

/**
 * Command sekali-pakai untuk mengisi data awal kelas (7A-7G, 8A-8H, dst)
 * dengan kapasitas 35 per kelas, untuk Tahun Ajaran yang sedang aktif.
 *
 * Aman dijalankan berkali-kali -- kelas yang namanya sudah ada di TA yang
 * sama akan di-skip, tidak dibuat dobel. Kalau tahun ajaran baru diaktifkan
 * dan mau dibuatkan struktur kelas yang sama, tinggal jalankan ulang.
 *
 * Jalankan: php artisan kelas:seed-awal
 */
class SeedKelasAwal extends Command
{
    protected $signature = 'kelas:seed-awal';

    protected $description = 'Buat data awal kelas (7A-7G, 8A-8H, 9A-9H, X-1..X-7, XI-1..XI-8, XII-1..XII-8) kapasitas 35 untuk TA aktif';

    /**
     * Peta tingkatan -> daftar nama kelas (rombel). Sesuaikan di sini kalau
     * ada perubahan jumlah rombel per tingkatan.
     */
    private array $data = [
        'Kelas 7'  => ['7A', '7B', '7C', '7D', '7E', '7F', '7G'],
        'Kelas 8'  => ['8A', '8B', '8C', '8D', '8E', '8F', '8G', '8H'],
        'Kelas 9'  => ['9A', '9B', '9C', '9D', '9E', '9F', '9G', '9H'],
        'Kelas 10' => ['X-1', 'X-2', 'X-3', 'X-4', 'X-5', 'X-6', 'X-7'],
        'Kelas 11' => ['XI-1', 'XI-2', 'XI-3', 'XI-4', 'XI-5', 'XI-6', 'XI-7', 'XI-8'],
        'Kelas 12' => ['XII-1', 'XII-2', 'XII-3', 'XII-4', 'XII-5', 'XII-6', 'XII-7', 'XII-8'],
    ];

    public function handle(): int
    {
        $ta = TahunAjaran::aktif();

        if (!$ta) {
            $this->error('Belum ada Tahun Ajaran aktif. Aktifkan dulu Tahun Ajaran sebelum menjalankan perintah ini.');
            return self::FAILURE;
        }

        $this->info("Tahun Ajaran aktif: {$ta->nama_lengkap}");
        $this->newLine();

        $dibuat = 0;
        $dilewati = 0;

        foreach ($this->data as $namaTingkatan => $rombelList) {
            $tingkatan = Tingkatan::where('nama', $namaTingkatan)->first();

            if (!$tingkatan) {
                $this->warn("Tingkatan '{$namaTingkatan}' tidak ditemukan di database -- semua rombel di bawahnya DILEWATI. Cek nama persis di menu Tingkatan (harus sama persis, termasuk kapitalisasi & spasi).");
                $dilewati += count($rombelList);
                continue;
            }

            foreach ($rombelList as $namaKelas) {
                $sudahAda = Kelas::where('nama', $namaKelas)
                    ->where('tahun_ajaran_id', $ta->id)
                    ->exists();

                if ($sudahAda) {
                    $this->line("  - {$namaKelas}: sudah ada, dilewati");
                    $dilewati++;
                    continue;
                }

                Kelas::create([
                    'nama'            => $namaKelas,
                    'tingkatan_id'    => $tingkatan->id,
                    'tahun_ajaran_id' => $ta->id,
                    'kapasitas'       => 35,
                ]);

                $this->info("  + {$namaKelas}: dibuat");
                $dibuat++;
            }
        }

        $this->newLine();
        $this->info("Selesai. {$dibuat} kelas dibuat, {$dilewati} dilewati.");

        return self::SUCCESS;
    }
}
