<?php
// ============================================================
// Santri.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;

    protected $table = 'santri';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'nama_arab',
        'nama_panggilan',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'asal_sekolah',
        'no_hp_santri',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'no_hp_wali',
        'pekerjaan_wali',
        'status',
        'angkatan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    // ---- Relations ----

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function santriKelas()
    {
        return $this->hasMany(SantriKelas::class);
    }

    public function kelasAktif()
    {
        return $this->hasOneThrough(
            Kelas::class,
            SantriKelas::class,
            'santri_id',
            'id',
            'id',
            'kelas_id'
        )->where('santri_kelas.status', 'aktif');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function nilaiAkhir()
    {
        return $this->hasMany(NilaiAkhir::class);
    }

    public function presensiKbm()
    {
        return $this->hasMany(PresensiKbm::class);
    }

    public function presensiKegiatan()
    {
        return $this->hasMany(PresensiKegiatan::class);
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function prestasi()
    {
        return $this->hasMany(Prestasi::class);
    }

    public function penempatanKamar()
    {
        return $this->hasMany(PenempatanKamar::class);
    }

    public function kamarAktif()
    {
        return $this->hasOneThrough(
            Kamar::class,
            PenempatanKamar::class,
            'santri_id',
            'id',
            'id',
            'kamar_id'
        )->where('penempatan_kamar.is_aktif', true);
    }

    // ---- Scopes ----

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByAngkatan($query, int $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    // ---- Helpers ----

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function getUmurAttribute(): int
    {
        return $this->tanggal_lahir?->age ?? 0;
    }

    public function getTotalPoinPelanggaranAttribute(): int
    {
        return $this->pelanggaran()
            ->where('status', 'aktif')
            ->join('kategori_pelanggaran', 'pelanggaran.kategori_pelanggaran_id', '=', 'kategori_pelanggaran.id')
            ->sum('kategori_pelanggaran.poin');
    }
    /**
     * Relasi ke data Penilaian SIMAQ (Tahsin/Tahfizh)
     */
    public function simaqPenilaians()
    {
        return $this->hasMany(\App\Models\SimaqPenilaian::class, 'santri_id');
    }
}
