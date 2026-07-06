<?php
// ============================================================
// app/Exports/PresensiExport.php
// FR-14: Export data presensi KBM ke Excel (per kelas & mata pelajaran)
// ============================================================
namespace App\Exports;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Services\PresensiKbmService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PresensiExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        protected Kelas $kelas,
        protected MataPelajaran $mataPelajaran,
        protected TahunAjaran $tahunAjaran,
        protected PresensiKbmService $presensiKbmService
    ) {}

    public function title(): string
    {
        return substr("Presensi {$this->kelas->nama}", 0, 31);
    }

    public function array(): array
    {
        $rekap = $this->presensiKbmService->getRekapKehadiranKelas(
            $this->kelas,
            $this->mataPelajaran,
            $this->tahunAjaran
        );

        $santriList = Santri::whereIn('id', $rekap->keys())
            ->orderBy('nama_lengkap')
            ->get()
            ->keyBy('id');

        $rows = [];
        $no   = 0;

        foreach ($rekap as $santriId => $data) {
            $santri = $santriList->get($santriId);
            if (! $santri) {
                continue;
            }

            $no++;
            $rows[] = [
                $no,
                $santri->nis,
                $santri->nama_lengkap,
                $data['hadir'],
                $data['sakit'],
                $data['izin'],
                $data['alpa'],
                $data['total'],
                $data['persen'] . '%',
                $data['tuntas'] ? 'Memenuhi Syarat' : 'Di Bawah Minimum',
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No', 'NIS', 'Nama Santri',
            'Hadir', 'Sakit', 'Izin', 'Alpa', 'Total Pertemuan',
            'Persentase Hadir', 'Status Kehadiran',
        ];
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
