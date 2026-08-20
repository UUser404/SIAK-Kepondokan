<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    // 'kkm' SENGAJA tidak ada di sini lagi -- KKM sekarang diatur per tingkatan
    // lewat tabel kkm_tingkatan (lihat kkmUntukTingkatan() di bawah), form
    // create/edit mapel tidak lagi mengirim field ini. Kolom `kkm` sendiri
    // TETAP ada di database & TETAP di $casts sebagai fallback -- jangan hapus
    // keduanya, lihat kkmUntukTingkatan().
    protected $fillable = ['kode', 'nama', 'kategori', 'deskripsi', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'kkm'       => 'integer',
        ];
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function nilaiAkhir()
    {
        return $this->hasMany(NilaiAkhir::class);
    }

    public function kkmTingkatan()
    {
        return $this->hasMany(KkmTingkatan::class);
    }

    /**
     * KKM untuk mapel ini di tingkatan tertentu -- sumber kebenaran BARU
     * (tabel kkm_tingkatan), karena KKM ternyata beda per tingkatan untuk
     * mapel yang sama. Fallback ke kolom `kkm` lama kalau kombinasi
     * mapel+tingkatan ini belum diisi di tabel baru (supaya tidak tiba-tiba
     * null di tempat yang masih perlu angka KKM).
     */
    public function kkmUntukTingkatan(?int $tingkatanId): ?int
    {
        if ($tingkatanId) {
            $kkm = $this->kkmTingkatan()->where('tingkatan_id', $tingkatanId)->value('kkm');
            if ($kkm !== null) {
                return $kkm;
            }
        }

        return $this->kkm;
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
