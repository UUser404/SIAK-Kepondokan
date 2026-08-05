<?php
// ============================================================
// TahunAjaran.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_rapor_hijriah',
        'nama_kepala_sekolah_arab',
        'nama_mudir_arab',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
            'is_active'       => 'boolean',
        ];
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public static function aktif(): ?self
    {
        return self::where('is_active', true)->first();
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->nama . ' - ' . ucfirst($this->semester);
    }
}
