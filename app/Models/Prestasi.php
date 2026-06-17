<?php
// ============================================================
// Prestasi.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestasi extends Model
{
    use HasFactory;

    protected $table = 'prestasi';

    protected $fillable = [
        'santri_id',
        'nama_prestasi',
        'jenis',
        'tingkat',
        'peringkat',
        'tanggal',
        'keterangan',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
