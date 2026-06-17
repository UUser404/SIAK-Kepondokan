<?php
// ============================================================
// Kamar.php & PenempatanKamar.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenempatanKamar extends Model
{
    use HasFactory;

    protected $table = 'penempatan_kamar';
    protected $fillable = [
        'santri_id',
        'kamar_id',
        'tahun_ajaran_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk'  => 'date',
            'tanggal_keluar' => 'date',
            'is_aktif'       => 'boolean',
        ];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }
}
