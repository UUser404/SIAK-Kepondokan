<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbBerkas extends Model
{
    use HasFactory;

    protected $table = 'ppdb_berkas';

    protected $fillable = [
        'ppdb_pendaftar_id',
        'jenis',
        'file_path',
        'status',
        'catatan',
        'diverifikasi_oleh',
        'diverifikasi_at',
    ];

    protected function casts(): array
    {
        return [
            'diverifikasi_at' => 'datetime',
        ];
    }

    /**
     * Label tampilan per jenis berkas -- SUMBER KEBENARAN TUNGGAL buat label
     * ini, dipakai di form upload publik & halaman verifikasi admin. Kalau
     * nambah jenis berkas baru, WAJIB tambah juga di enum kolom `jenis`
     * (migration) DAN di sini -- 2 tempat itu harus sinkron.
     */
    public const LABEL = [
        'akta_kelahiran'         => 'Akta Kelahiran',
        'kartu_keluarga'         => 'Kartu Keluarga (KK)',
        'ktp_ayah'                => 'KTP Ayah',
        'ktp_ibu'                 => 'KTP Ibu',
        'ktp_wali'                => 'KTP Wali',
        'ijazah_skl'              => 'Ijazah / SKL',
        'rapor_semester_1'        => 'Rapor Semester 1 (Terakhir)',
        'rapor_semester_2'        => 'Rapor Semester 2 (Terakhir)',
        'surat_keterangan_sehat'  => 'Surat Keterangan Sehat',
        'piagam_prestasi'         => 'Piagam / Sertifikat Prestasi',
    ];

    /**
     * Jenis berkas yang WAJIB ada buat semua pendaftar, apapun jalurnya.
     * 'ijazah_skl' SENGAJA tidak masuk sini -- itu cuma wajib buat jenjang
     * SMA (dicek terpisah di logic form/validasi, bukan di sini, karena
     * accessor ini tidak tahu konteks 1 pendaftar tertentu).
     */
    public const WAJIB_SEMUA = [
        'akta_kelahiran',
        'kartu_keluarga',
        'ktp_ayah',
        'ktp_ibu',
        'rapor_semester_1',
        'rapor_semester_2',
        'surat_keterangan_sehat',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(PpdbPendaftar::class, 'ppdb_pendaftar_id');
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function getLabelAttribute(): string
    {
        return self::LABEL[$this->jenis] ?? $this->jenis;
    }
}
