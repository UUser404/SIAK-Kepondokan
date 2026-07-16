<?php
// ============================================================
// app/Models/Ekstrakurikuler.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = ['nama', 'pembina', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }
}
