<?php
// ============================================================
// app/Models/KkmTingkatan.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KkmTingkatan extends Model
{
    protected $table = 'kkm_tingkatan';

    protected $fillable = ['mata_pelajaran_id', 'tingkatan_id', 'kkm'];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function tingkatan()
    {
        return $this->belongsTo(Tingkatan::class);
    }
}
