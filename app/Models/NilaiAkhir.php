<?php
// ============================================================
// NilaiAkhir.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiAkhir extends Model
{
    use HasFactory;

    protected $table = 'nilai_akhir';

    protected $fillable = [
        'santri_id',
        'kelas_id',
        'mata_pelajaran_id',
        'tahun_ajaran_id',
        'nilai_uh',
        'nilai_tugas',
        'nilai_praktik',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
        'tuntas',
        'catatan_guru',
    ];

    protected function casts(): array
    {
        return [
            'nilai_uh'      => 'decimal:2',
            'nilai_tugas'   => 'decimal:2',
            'nilai_praktik' => 'decimal:2',
            'nilai_uts'     => 'decimal:2',
            'nilai_uas'     => 'decimal:2',
            'nilai_akhir'   => 'decimal:2',
            'tuntas'        => 'boolean',
        ];
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

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
