<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai'   => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class);
    }

    /**
     * Cek apakah jadwal ini sudah punya pertemuan hari ini
     */
    public function getSudahPresensiAttribute(): bool
    {
        return $this->pertemuan()
            ->whereDate('tanggal', today())
            ->exists();
    }
}
