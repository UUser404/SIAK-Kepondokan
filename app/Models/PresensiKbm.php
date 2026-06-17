<?php
// ============================================================
// PresensiKbm.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiKbm extends Model
{
    use HasFactory;

    protected $table = 'presensi_kbm';

    protected $fillable = [
        'pertemuan_id',
        'santri_id',
        'status',
        'keterangan',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeAlpa($query)
    {
        return $query->where('status', 'alpa');
    }
}
