<?php
// ============================================================
// app/Exports/NilaiExport.php
// FR-14: Export data nilai ke Excel (per kelas & mata pelajaran)
// ============================================================
namespace App\Exports;

use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NilaiExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $komponenKode;

    public function __construct(
        protected Kelas $kelas,
        protected MataPelajaran $mataPelajaran,
        protected TahunAjaran $tahunAjaran,
        protected PenilaianService $penilaianService
    ) {
        // Dihitung di awal (bukan di array()) supaya urutan pemanggilan
        // headings() vs array() oleh package Excel tidak memengaruhi hasil.
        $this->komponenKode = KomponenNilai::where('is_active', true)
            ->orderBy('urutan')
            ->pluck('kode')
            ->all();
    }

    public function title(): string
    {
        return substr("Nilai {$this->kelas->nama}", 0, 31);
    }

    public function array(): array
    {
        $rekap = $this->penilaianService->getRekapNilaiKelas(
            $this->kelas,
            $this->mataPelajaran,
            $this->tahunAjaran
        );

        $rows = [];
        $no   = 0;

        foreach ($rekap['rows'] as $row) {
            $no++;
            $line = [
                $no,
                $row['santri']->nis,
                $row['santri']->nama_lengkap,
            ];

            foreach ($this->komponenKode as $kode) {
                $line[] = $row['komponen'][$kode] !== null ? round($row['komponen'][$kode], 2) : '-';
            }

            $line[] = $row['nilai_akhir']?->nilai_akhir ?? '-';
            $line[] = $row['nilai_akhir']?->predikat ?? '-';
            $line[] = $row['nilai_akhir']?->tuntas ? 'Tuntas' : 'Belum Tuntas';

            $rows[] = $line;
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['No', 'NIS', 'Nama Santri'];

        foreach ($this->komponenKode as $kode) {
            $headings[] = $kode;
        }

        $headings[] = 'Nilai Akhir';
        $headings[] = 'Predikat';
        $headings[] = 'Status';

        return $headings;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '234C6A']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 15, 'C' => 30,
        ];
    }
}
