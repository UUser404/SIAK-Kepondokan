<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', 'like', "%{$action}%");
    }

    /**
     * Kalimat aktivitas Bahasa Indonesia yang mudah dibaca -- BUKAN
     * string teknis mentah dari kolom `action`. `action` disimpan lewat
     * strtolower(class_basename($model)) di ActivityLogService, yang
     * cuma aman buat model 1 kata (mis. "santri.created"). Buat model
     * 2 kata atau lebih, strtolower() menghapus SEMUA sinyal pemisah
     * kata (huruf besar), jadi "KategoriMataPelajaran" -> "kategori-
     * matapelajaran" -- satu kata nyambung, nyaris tidak kebaca.
     *
     * Accessor ini TIDAK baca `action` buat nama modelnya -- ambil dari
     * `model_type` (nama class asli, PascalCase utuh, tidak pernah
     * di-lowercase), terus dipisah per kata pakai regex. Cuma segmen
     * TERAKHIR dari `action` (dipisah '.') yang dipakai, itu buat kata
     * kerjanya (created/updated/deleted) -- ini konsisten dipakai baik
     * buat action otomatis ("modelname.created") maupun action manual
     * custom kayak "presensi_kbm.updated" (segmen terakhirnya tetap
     * "updated" walau segmen depannya beda pola dari yang lain).
     */
    public function getDeskripsiAttribute(): string
    {
        $verbMap = [
            'created' => 'menambahkan',
            'updated' => 'mengubah',
            'deleted' => 'menghapus',
        ];

        $segments = explode('.', (string) $this->action);
        $verbKey  = end($segments);
        $verb     = $verbMap[$verbKey] ?? $verbKey;

        if ($this->model_type) {
            $namaModel = class_basename($this->model_type);

            // Pisah PascalCase jadi kata per kata: "KategoriMataPelajaran"
            // -> "Kategori Mata Pelajaran". Karena nama model di project
            // ini memang sudah Bahasa Indonesia, ini cukup tanpa perlu
            // daftar terjemahan manual per model.
            $namaTampil = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $namaModel));

            // Perbaikan kecil buat singkatan yang harusnya full uppercase
            // (regex di atas cuma pertahanin huruf besar PERTAMA tiap
            // kata, jadi "Kkm"/"Kbm", bukan "KKM"/"KBM").
            $namaTampil = strtr($namaTampil, [
                'Kkm' => 'KKM',
                'Kbm' => 'KBM',
                'User' => 'Pengguna',
            ]);
        } else {
            // Tidak ada model terkait -- pakai segmen action SEBELUM kata
            // kerja terakhir sebagai fallback (mis. action manual tanpa
            // model sama sekali).
            array_pop($segments);
            $namaTampil = $segments
                ? ucwords(str_replace('_', ' ', implode(' ', $segments)))
                : 'data';
        }

        return "{$verb} {$namaTampil}";
    }
}
