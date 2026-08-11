<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriMataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'kategori_mata_pelajaran';

    protected $fillable = ['nama', 'urutan'];

    /**
     * Relasi "lunak" (bukan foreign key di database) ke MataPelajaran --
     * mata_pelajaran.kategori tetap kolom string, dicocokkan ke nama kategori
     * di sini. Tetap bisa dipakai seperti relasi biasa (withCount, exists, dst)
     * karena Eloquent hanya butuh kecocokan kolom, tidak wajib ada FK constraint.
     */
    public function mataPelajaran()
    {
        return $this->hasMany(MataPelajaran::class, 'kategori', 'nama');
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }
}