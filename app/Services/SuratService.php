<?php
// ============================================================
// app/Services/SuratService.php
// ============================================================
namespace App\Services;

use App\Models\SuratKeluar;
use App\Models\TemplateSurat;
use App\Models\Santri;
use Carbon\Carbon;

class SuratService
{
    /**
     * Generate nomor surat otomatis
     * Format: PP-AI / {No} / {Bulan Romawi} / {Tahun}
     */
    public function generateNomor(): string
    {
        $prefix  = config('siak.surat.prefix_nomor', 'PP-AI');
        $tahun   = now()->year;
        $bulanRomawi = $this->bulanRomawi(now()->month);

        $urutan = SuratKeluar::whereYear('created_at', $tahun)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;

        return "{$prefix}/{$urutan}/{$bulanRomawi}/{$tahun}";
    }

    /**
     * Render template dengan placeholder diganti data nyata
     */
    public function renderTemplate(string $konten, array $data): string
    {
        $placeholders = [
            '{{nomor_surat}}'       => $data['nomor_surat']       ?? '',
            '{{tanggal_surat}}'     => $data['tanggal_surat'] instanceof Carbon
                ? $data['tanggal_surat']->locale('id')->isoFormat('D MMMM Y')
                : ($data['tanggal_surat'] ?? ''),
            '{{perihal}}'           => $data['perihal']            ?? '',
            '{{ditujukan_kepada}}'  => $data['ditujukan_kepada']   ?? '',
            // Data santri (jika ada)
            '{{nama_santri}}'       => $data['nama_santri']        ?? '',
            '{{nis}}'               => $data['nis']                ?? '',
            '{{kelas}}'             => $data['kelas']              ?? '',
            '{{nama_wali}}'         => $data['nama_wali']          ?? '',
            '{{no_hp_wali}}'        => $data['no_hp_wali']         ?? '',
            // Data pondok
            '{{nama_pondok}}'       => config('siak.pondok.nama'),
            '{{alamat_pondok}}'     => config('siak.pondok.alamat', ''),
            '{{kepala_pondok}}'     => config('siak.pondok.kepala', ''),
            '{{tahun}}'             => now()->year,
            '{{bulan}}'             => now()->locale('id')->isoFormat('MMMM'),
        ];

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $konten
        );
    }

    /**
     * Build data untuk render dari model SuratKeluar
     */
    public function buildDataFromSurat(SuratKeluar $surat): array
    {
        $data = [
            'nomor_surat'      => $surat->nomor_surat,
            'tanggal_surat'    => $surat->tanggal_surat,
            'perihal'          => $surat->perihal,
            'ditujukan_kepada' => $surat->ditujukan_kepada,
        ];

        if ($surat->santri_id) {
            $santri = Santri::with(['santriKelas.kelas'])->find($surat->santri_id);
            if ($santri) {
                $data = array_merge($data, [
                    'nama_santri' => $santri->nama_lengkap,
                    'nis'         => $santri->nis,
                    'kelas'       => $santri->santriKelas->where('status', 'aktif')->first()?->kelas?->nama ?? '-',
                    'nama_wali'   => $santri->nama_wali ?? $santri->nama_ayah ?? '-',
                    'no_hp_wali'  => $santri->no_hp_wali ?? '-',
                ]);
            }
        }

        return $data;
    }

    /**
     * Konversi bulan ke angka Romawi
     */
    private function bulanRomawi(int $bulan): string
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawi[$bulan - 1] ?? 'I';
    }
}
