<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'kategori_pelanggaran';

    protected $fillable = ['nama', 'tingkat', 'poin', 'deskripsi'];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
        ];
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function scopeRingan($query)
    {
        return $query->where('tingkat', 'ringan');
    }
    public function scopeSedang($query)
    {
        return $query->where('tingkat', 'sedang');
    }
    public function scopeBerat($query)
    {
        return $query->where('tingkat', 'berat');
    }
}
