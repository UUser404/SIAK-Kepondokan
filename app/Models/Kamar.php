<?php
// ============================================================
// Kamar.php & PenempatanKamar.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar';
    protected $fillable = ['asrama_id', 'nomor_kamar', 'kapasitas', 'lantai', 'is_active'];

    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function penempatanAktif()
    {
        return $this->hasMany(PenempatanKamar::class)->where('is_aktif', true);
    }

    public function penghuni()
    {
        return $this->belongsToMany(Santri::class, 'penempatan_kamar')
            ->wherePivot('is_aktif', true);
    }

    public function getSisaKapasitasAttribute(): int
    {
        return $this->kapasitas - $this->penempatanAktif()->count();
    }
}
