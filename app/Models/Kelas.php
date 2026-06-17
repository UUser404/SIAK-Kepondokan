<?php
// ============================================================
// Kelas.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'tingkatan_id',
        'tahun_ajaran_id',
        'wali_kelas_id',
        'kapasitas',
    ];

    public function tingkatan()
    {
        return $this->belongsTo(Tingkatan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function santriKelas()
    {
        return $this->hasMany(SantriKelas::class);
    }

    public function santri()
    {
        return $this->belongsToMany(Santri::class, 'santri_kelas')
            ->wherePivot('status', 'aktif');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class);
    }

    public function getJumlahSantriAttribute(): int
    {
        return $this->santriKelas()->where('status', 'aktif')->count();
    }
}
