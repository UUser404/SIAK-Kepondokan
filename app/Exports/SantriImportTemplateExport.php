<?php
// ============================================================
// app/Exports/SantriImportTemplateExport.php
// Template kosong untuk diisi admin sebelum diupload lewat fitur
// Import Kelas & Asrama Massal. Header di sini SENGAJA pakai teks
// manusiawi ("NIS", "Nama Lengkap", "Nama Arab", dst) -- saat file ini
// diupload balik, Maatwebsite\Excel (WithHeadingRow, formatter default)
// otomatis nge-slug jadi nis/nama_lengkap/nama_arab/kelas/asrama, yang
// cocok dengan key yang dibaca SantriImportService::processRow().
// ============================================================
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SantriImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import';
    }

    public function headings(): array
    {
        return ['NIS', 'Nama Lengkap', 'Nama Arab', 'Kelas', 'Asrama'];
    }

    public function array(): array
    {
        return [
            // Baris contoh -- hapus/timpa dengan data asli sebelum upload.
            // Kolom Nama Arab, Kelas & Asrama boleh dikosongkan kalau tidak
            // ada perubahan untuk santri itu (NIS tetap wajib diisi supaya
            // bisa dicocokkan).
            ['2024001', 'Contoh Nama Santri', 'اسم الطالب المثال', '8B', 'Asrama Al-Fatih'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // NIS
            'B' => 30,  // Nama Lengkap
            'C' => 25,  // Nama Arab (baru)
            'D' => 12,  // Kelas
            'E' => 20,  // Asrama
        ];
    }
}
