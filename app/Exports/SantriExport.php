<?php
// ============================================================
// app/Exports/SantriExport.php
// ============================================================
namespace App\Exports;

use App\Models\Santri;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SantriExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Data Santri';
    }

    public function query()
    {
        $ta = TahunAjaran::aktif();

        return Santri::with(['santriKelas.kelas', 'penempatanKamar.kamar.asrama'])
            ->when(isset($this->filters['status']), fn($q) => $q->where('status', $this->filters['status']))
            ->when(isset($this->filters['kelas_id']) && $ta, fn($q) => $q->whereHas(
                'santriKelas',
                fn($k) =>
                $k->where('kelas_id', $this->filters['kelas_id'])
                    ->where('tahun_ajaran_id', $ta->id)
                    ->where('status', 'aktif')
            ))
            ->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Nama Panggilan',
            'L/P',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Kelas',
            'Asrama',
            'Kamar',
            'Angkatan',
            'Asal Sekolah',
            'Nama Wali',
            'No. HP Wali',
            'Status',
        ];
    }

    public function map($santri): array
    {
        static $no = 0;
        $no++;

        $kelas = $santri->santriKelas->where('status', 'aktif')->first()?->kelas?->nama ?? '-';
        $penempatanAktif = $santri->penempatanKamar->where('is_aktif', true)->first();
        $asrama = $penempatanAktif?->kamar?->asrama?->nama ?? '-';
        $kamar = $penempatanAktif?->kamar?->nomor_kamar ?? '-';

        return [
            $no,
            $santri->nis,
            $santri->nisn ?? '-',
            $santri->nama_lengkap,
            $santri->nama_panggilan ?? '-',
            $santri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $santri->tempat_lahir ?? '-',
            $santri->tanggal_lahir?->format('d/m/Y') ?? '-',
            $kelas,
            $asrama,
            $kamar,
            $santri->angkatan ?? '-',
            $santri->asal_sekolah ?? '-',
            $santri->nama_wali ?? $santri->nama_ayah ?? '-',
            $santri->no_hp_wali ?? '-',
            ucfirst($santri->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 15,
            'C' => 15,
            'D' => 30,
            'E' => 15,
            'F' => 12,
            'G' => 18,
            'H' => 15,
            'I' => 10,
            'J' => 18,
            'K' => 12,
            'L' => 10,
            'M' => 25,
            'N' => 25,
            'O' => 18,
            'P' => 12,
        ];
    }
}
