<?php
// ============================================================
// TenagaPendidik.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenagaPendidik extends Model
{
    use HasFactory;

    protected $table = 'tenaga_pendidik';

    protected $fillable = [
        'user_id',
        'nip',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'pendidikan_terakhir',
        'jurusan',
        'status_kepegawaian',
        'tanggal_masuk',
        'foto',
        'kategori_guru',
        'is_simaq_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'  => 'date',
            'tanggal_masuk'  => 'date',
            'is_simaq_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'guru_id', 'user_id');
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class, 'guru_id', 'user_id');
    }

    public function simaqPenilaians()
    {
        return $this->hasMany(SimaqPenilaian::class, 'guru_id', 'id');
    }

    // ---- Scopes ----

    public function scopeSimaqActive($query)
    {
        return $query->where('is_simaq_active', true);
    }

    public function scopeKategoriSimaq($query)
    {
        return $query->whereIn('kategori_guru', ['tahsin', 'tahfizh']);
    }
}
