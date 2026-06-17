<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tingkatan extends Model
{
    use HasFactory;

    protected $table = 'tingkatan';

    protected $fillable = ['nama', 'urutan'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }
}
