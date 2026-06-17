<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbPendaftar extends Model
{
    use HasFactory;

    protected $table = 'ppdb_pendaftar';

    protected $fillable = [
        'ppdb_periode_id',
        'nomor_daftar',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'asal_sekolah',
        'nisn',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'no_hp_wali',
        'email_wali',
        'alamat',
        'status',
        'catatan_admin',
        'tanggal_verifikasi',
        'diverifikasi_oleh',
        'santri_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'       => 'date',
            'tanggal_verifikasi'  => 'datetime',
        ];
    }

    public function ppdbPeriode()
    {
        return $this->belongsTo(PpdbPeriode::class);
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }
    public function scopeDiterima($query)
    {
        return $query->where('status', 'diterima');
    }
}
