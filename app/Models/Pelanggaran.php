<?php
// ============================================================
// Pelanggaran.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran';

    protected $fillable = [
        'santri_id',
        'kategori_pelanggaran_id',
        'tanggal',
        'deskripsi',
        'sanksi',
        'status',
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

    public function kategori()
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_pelanggaran_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
