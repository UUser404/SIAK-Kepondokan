<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'kkm', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'kkm'       => 'integer',
        ];
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function nilaiAkhir()
    {
        return $this->hasMany(NilaiAkhir::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }
}
