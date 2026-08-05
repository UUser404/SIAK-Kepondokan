<?php
// ============================================================
// app/Models/PredikatSikap.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredikatSikap extends Model
{
    use HasFactory;

    protected $table = 'predikat_sikap';

    protected $fillable = [
        'santri_id',
        'kelas_id',
        'tahun_ajaran_id',
        'kedisiplinan',
        'kebersihan',
        'kerajinan',
        'akhlak',
        'sakit_override',
        'izin_override',
        'alpa_override',
        'catatan_wali_kelas',
        'diinput_oleh',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}