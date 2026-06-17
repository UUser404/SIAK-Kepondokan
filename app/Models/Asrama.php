<?php
// ============================================================
// Kamar.php & PenempatanKamar.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asrama extends Model
{
    use HasFactory;

    protected $table = 'asrama';
    protected $fillable = ['nama', 'jenis', 'pengurus', 'keterangan', 'is_active'];

    public function kamar()
    {
        return $this->hasMany(Kamar::class);
    }

    public function getJumlahHuniAttribute(): int
    {
        return $this->kamar()->withCount(['penempatanAktif'])->get()
            ->sum('penempatan_aktif_count');
    }
}
