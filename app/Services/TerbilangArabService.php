<?php
// ============================================================
// app/Services/TerbilangArabService.php
// Konversi angka (bilangan bulat 0-100) ke kata Arab, dari lookup
// config('terbilang_arab') yang diekstrak dari file template asli.
// ============================================================
namespace App\Services;

class TerbilangArabService
{
    /**
     * @param float|int $angka Nilai 0-100. Desimal dibulatkan dulu ke bilangan
     *   bulat terdekat (lookup cuma untuk bilangan bulat, sesuai keputusan awal).
     */
    public function kataArab(float|int $angka): string
    {
        $bulat = (int) round($angka);

        if ($bulat <= 0) {
            return 'صِفْرٌ'; // nol -- tidak ada di lookup asli (1-100 saja), ditambahkan manual
        }

        if ($bulat > 100) {
            $bulat = 100;
        }

        return config("terbilang_arab.{$bulat}", (string) $bulat);
    }
}
