<?php
// ============================================================
// app/Models/NilaiEkstrakurikuler.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiEkstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'nilai_ekstrakurikuler';

    protected $fillable = [
        'santri_id',
        'ekstrakurikuler_id',
        'kelas_id',
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

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
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

    /**
     * Predikat kualitatif (Sangat Baik/Baik/Cukup/Kurang) dari nilai angka,
     * sesuai config('siak.ekstrakurikuler.predikat').
     */
    public function getPredikatAttribute(): string
    {
        return app(\App\Services\PenilaianService::class)->getPredikatEkstrakurikuler((float) $this->nilai);
    }
}
