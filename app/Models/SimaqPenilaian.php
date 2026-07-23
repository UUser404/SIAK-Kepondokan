<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SimaqPenilaian Model
 * 
 * Model untuk menyimpan semua catatan penilaian SIMAQ (Sistem Penilaian Al-Quran).
 * Menggunakan SoftDeletes untuk audit trail dan data recovery.
 */
class SimaqPenilaian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'simaq_penilaians';

    protected $fillable = [
        'santri_id',
        'guru_id',
        'kelas_id',
        'program',
        'jenis',
        'tanggal',
        'surah_ayat',
        'halaman',
        'juz',
        'kesalahan_kelancaran',
        'kesalahan_tajwid',
        'kesalahan_makhraj',
        'nilai_kelancaran',
        'nilai_tajwid',
        'nilai_makhraj',
        'nilai_akhir',
        'bintang',
        'huruf',
        'predikat',
        'status_kelulusan',
        'catatan',
    ];

    /**
     * Type casting untuk kolom-kolom tertentu
     */
    protected function casts(): array
    {
        return [
            'tanggal'               => 'date',
            'nilai_kelancaran'      => 'float',
            'nilai_tajwid'          => 'float',
            'nilai_makhraj'         => 'float',
            'nilai_akhir'           => 'float',
            'kesalahan_kelancaran'  => 'integer',
            'kesalahan_tajwid'      => 'integer',
            'kesalahan_makhraj'     => 'integer',
            'bintang'               => 'integer',
        ];
    }

    // ========================================
    // Relations
    // ========================================

    /**
     * Relasi ke Santri - banyak penilaian untuk satu santri
     */
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    /**
     * Relasi ke Guru (TenagaPendidik) - banyak penilaian untuk satu guru
     */
    public function guru()
    {
        return $this->belongsTo(TenagaPendidik::class, 'guru_id', 'id');
    }

    /**
     * Relasi ke Kelas - banyak penilaian untuk satu kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Filter penilaian harian (setoran_harian)
     */
    public function scopeHarian($query)
    {
        return $query->where('jenis', 'setoran_harian');
    }

    /**
     * Filter penilaian ujian (tasmi, pemantapan)
     */
    public function scopeUjian($query)
    {
        return $query->whereIn('jenis', ['tasmi', 'pemantapan']);
    }

    /**
     * Filter by program tertentu
     */
    public function scopeByProgram($query, string $program)
    {
        return $query->where('program', $program);
    }

    /**
     * Filter penilaian yang LULUS
     */
    public function scopeLulus($query)
    {
        return $query->whereIn('status_kelulusan', ['Lulus', 'Lulus (Mutqin)']);
    }

    /**
     * Filter penilaian yang TIDAK LULUS
     */
    public function scopeTidakLulus($query)
    {
        return $query->where('status_kelulusan', 'Tidak Lulus');
    }

    /**
     * Filter penilaian dengan nilai >= threshold
     */
    public function scopeNilaiMin($query, float $minValue)
    {
        return $query->where('nilai_akhir', '>=', $minValue);
    }

    /**
     * Filter penilaian dari guru tertentu
     */
    public function scopeByGuru($query, int $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Filter penilaian untuk santri tertentu
     */
    public function scopeBySantri($query, int $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    /**
     * Filter penilaian dalam rentang tanggal
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // ========================================
    // Accessors & Mutators
    // ========================================

    /**
     * Get formatted tanggal display
     */
    public function getTanggalDisplayAttribute(): string
    {
        return $this->tanggal?->format('d M Y') ?? '-';
    }

    /**
     * Get status kelulusan badge color untuk UI
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status_kelulusan) {
            'Lulus (Mutqin)' => 'success',
            'Lulus'          => 'info',
            'Tidak Lulus'    => 'danger',
            default          => 'secondary',
        };
    }

    /**
     * Get bintang display dengan icon
     */
    public function getBintangDisplayAttribute(): string
    {
        if (!$this->bintang) {
            return '-';
        }
        return str_repeat('⭐', $this->bintang);
    }

    // ========================================
    // Custom Methods
    // ========================================

    /**
     * Apakah ini penilaian harian?
     */
    public function isHarian(): bool
    {
        return $this->jenis === 'setoran_harian';
    }

    /**
     * Apakah ini penilaian ujian?
     */
    public function isUjian(): bool
    {
        return in_array($this->jenis, ['tasmi', 'pemantapan']);
    }

    /**
     * Check apakah penilaian sudah lengkap (semua komponen terisi)
     */
    public function isLengkap(): bool
    {
        return $this->nilai_akhir !== null
            && $this->huruf !== null
            && $this->predikat !== null;
    }

    /**
     * Get total kesalahan
     */
    public function getTotalKesalahanAttribute(): int
    {
        return $this->kesalahan_kelancaran
            + $this->kesalahan_tajwid
            + $this->kesalahan_makhraj;
    }
}
