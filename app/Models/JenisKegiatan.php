<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKegiatan extends Model
{
    use HasFactory;

    protected $table = 'jenis_kegiatan';

    protected $fillable = ['nama', 'deskripsi', 'waktu_default', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function presensiKegiatan()
    {
        return $this->hasMany(PresensiKegiatan::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->orderBy('waktu_default');
    }
}
