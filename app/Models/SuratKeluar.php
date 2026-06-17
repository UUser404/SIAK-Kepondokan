<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'template_surat_id',
        'nomor_surat',
        'perihal',
        'ditujukan_kepada',
        'santri_id',
        'tanggal_surat',
        'konten',
        'status',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
        ];
    }

    public function templateSurat()
    {
        return $this->belongsTo(TemplateSurat::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isDiterbitkan(): bool
    {
        return $this->status === 'diterbitkan';
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    public function scopeDiterbitkan($query)
    {
        return $query->where('status', 'diterbitkan');
    }
}
