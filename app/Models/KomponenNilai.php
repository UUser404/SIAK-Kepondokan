<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenNilai extends Model
{
    use HasFactory;

    protected $table = 'komponen_nilai';

    protected $fillable = ['nama', 'kode', 'bobot', 'urutan', 'is_active'];

    protected function casts(): array
    {
        return [
            'bobot'     => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }
}
