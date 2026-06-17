<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbPeriode extends Model
{
    use HasFactory;

    protected $table = 'ppdb_periode';

    protected $fillable = [
        'nama',
        'tahun_ajaran_id',
        'tanggal_buka',
        'tanggal_tutup',
        'kuota',
        'is_active',
        'persyaratan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_buka'    => 'date',
            'tanggal_tutup'   => 'date',
            'is_active'       => 'boolean',
            'kuota'           => 'integer',
        ];
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function pendaftar()
    {
        return $this->hasMany(PpdbPendaftar::class);
    }

    public function getSisaKuotaAttribute(): int
    {
        return $this->kuota - $this->pendaftar()
            ->where('status', 'diterima')->count();
    }

    public function getIsBukaAttribute(): bool
    {
        return $this->is_active
            && now()->between($this->tanggal_buka, $this->tanggal_tutup);
    }
}
