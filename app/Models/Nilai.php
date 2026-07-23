<?php
// ============================================================
// Nilai.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';

    protected $fillable = [
        'santri_id',
        'kelas_id',
        'mata_pelajaran_id',
        'komponen_nilai_id',
        'slot',
        'tahun_ajaran_id',
        'nilai',
        'catatan',
        'diinput_oleh',
    ];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2'];
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function komponenNilai()
    {
        return $this->belongsTo(KomponenNilai::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function inputOleh()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
