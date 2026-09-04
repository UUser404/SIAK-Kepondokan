<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PpdbPendaftar extends Model
{
    use HasFactory;

    protected $table = 'ppdb_pendaftar';

    protected $fillable = [
        'ppdb_periode_id',
        'nomor_daftar',
        // Jalur
        'jalur',
        'bidang_prestasi',
        'tingkat_prestasi',
        'tahun_prestasi',
        // Data calon siswa
        'jenjang',
        'nama_lengkap',
        'nama_arab',
        'nik',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'anak_ke',
        'dari_bersaudara',
        'golongan_darah',
        'asal_sekolah',
        'asal_provinsi',
        'alamat',
        'foto',
        // Kesehatan
        'riwayat_penyakit',
        'alergi_makanan',
        'alergi_obat',
        'obat_rutin',
        // Keluarga
        'no_kk',
        'nama_ayah',
        'nik_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'pendidikan_ayah',
        'no_hp_ayah',
        'nama_ibu',
        'nik_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'pendidikan_ibu',
        'no_hp_ibu',
        'nama_wali',
        'hubungan_wali',
        'nik_wali',
        'no_hp_wali',
        'alamat_wali',
        'nama_kontak_darurat',
        'hubungan_kontak_darurat',
        'no_hp_kontak_darurat',
        // Riwayat pendidikan agama
        'pernah_tpa',
        'pernah_mondok',
        'nama_pesantren_asal',
        'estimasi_hafalan_juz',
        // Sumber informasi
        'sumber_informasi',
        'sumber_informasi_lainnya',
        // Status berkas
        'status_berkas',
        'catatan_berkas',
        'diverifikasi_berkas_oleh',
        'diverifikasi_berkas_at',
        // Status pembayaran
        'status_pembayaran',
        'bukti_pembayaran',
        'catatan_pembayaran',
        'diverifikasi_pembayaran_oleh',
        'diverifikasi_pembayaran_at',
        // Status akhir
        'status_akhir',
        'catatan_admin',
        'qr_token',
        'santri_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'                => 'date',
            'pernah_tpa'                   => 'boolean',
            'pernah_mondok'                => 'boolean',
            'penghasilan_ayah'             => 'integer',
            'penghasilan_ibu'              => 'integer',
            'estimasi_hafalan_juz'         => 'integer',
            'diverifikasi_berkas_at'       => 'datetime',
            'diverifikasi_pembayaran_at'   => 'datetime',
        ];
    }

    // ==========================================
    // Relations
    // ==========================================

    public function ppdbPeriode()
    {
        return $this->belongsTo(PpdbPeriode::class);
    }

    public function berkas()
    {
        return $this->hasMany(PpdbBerkas::class, 'ppdb_pendaftar_id');
    }

    public function diverifikasiBerkasOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_berkas_oleh');
    }

    public function diverifikasiPembayaranOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_pembayaran_oleh');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeBerkasMenunggu($query)
    {
        return $query->where('status_berkas', 'menunggu');
    }

    public function scopeSiapDibayar($query)
    {
        return $query->where('status_berkas', 'terverifikasi')
            ->where('status_pembayaran', 'belum_bayar');
    }

    public function scopeMenungguVerifikasiPembayaran($query)
    {
        return $query->where('status_pembayaran', 'menunggu_verifikasi');
    }

    public function scopeDiterima($query)
    {
        return $query->where('status_akhir', 'diterima');
    }

    // ==========================================
    // Helper -- dipakai buat nentuin arahan apa yang muncul di halaman cek
    // status publik (§ lihat PpdbPublicController nanti). SENGAJA jadi
    // method di model (bukan logic di controller/view) supaya "apa artinya
    // kombinasi status ini" cuma didefinisikan SEKALI, tidak ke-duplikasi
    // antara halaman cek status & halaman admin.
    // ==========================================

    /**
     * True kalau berkas sudah lolos verifikasi DAN belum ada pembayaran yang
     * masih diproses -- ini yang nentuin apakah arahan pembayaran ditampilkan
     * ke pendaftar di halaman cek status.
     */
    public function siapUntukBayar(): bool
    {
        return $this->status_berkas === 'terverifikasi'
            && in_array($this->status_pembayaran, ['belum_bayar', 'ditolak'], true);
    }

    public function sudahDiterima(): bool
    {
        return $this->status_akhir === 'diterima';
    }

    /**
     * Generate token QR acak & unik -- dipanggil sekali waktu status_akhir
     * berubah jadi 'diterima' (di controller, bukan otomatis di sini lewat
     * event/observer, supaya jelas kapan persisnya token ini dibuat).
     * Detail PEMAKAIAN token ini (kartu peserta, dsb) belum dirancang --
     * method ini cuma nyiapin token-nya, bukan generate gambar QR/PDF-nya.
     */
    public function generateQrToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('qr_token', $token)->exists());

        $this->update(['qr_token' => $token]);

        return $token;
    }
}
