<?php
// ============================================================
// PresensiKegiatan.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'presensi_kegiatan';

    protected $fillable = [
        'santri_id',
        'jenis_kegiatan_id',
        'tanggal',
        'status',
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

    public function jenisKegiatan()
    {
        return $this->belongsTo(JenisKegiatan::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
