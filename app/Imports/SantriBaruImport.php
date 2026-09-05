<?php
// ============================================================
// app/Imports/SantriBaruImport.php
// ============================================================
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriBaruImport implements ToCollection, WithHeadingRow
{
    /**
     * Parse Excel file dan return collection of data rows.
     * Header yang diharapkan: NISN, Nama Lengkap, Kelas, Angkatan, Jenis Kelamin
     * (Maatwebsite\Excel WithHeadingRow otomatis nge-slug jadi
     * nisn/nama_lengkap/kelas/angkatan/jenis_kelamin).
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
}
