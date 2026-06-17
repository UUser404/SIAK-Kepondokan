<?php
// ============================================================
// Pertemuan.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    use HasFactory;

    protected $table = 'pertemuan';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'guru_id',
        'kelas_id',
        'mata_pelajaran_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'pertemuan_ke',
        'topik',
        'materi',
        'catatan_guru',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'   => 'date',
            'jam_mulai' => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function presensiKbm()
    {
        return $this->hasMany(PresensiKbm::class);
    }

    public function getJumlahHadirAttribute(): int
    {
        return $this->presensiKbm()->where('status', 'hadir')->count();
    }

    public function getJumlahAlpaAttribute(): int
    {
        return $this->presensiKbm()->where('status', 'alpa')->count();
    }
}
