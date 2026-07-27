<?php
// ============================================================
// app/Imports/SantriKelasAsramaImport.php
// ============================================================
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriKelasAsramaImport implements ToCollection, WithHeadingRow
{
    /**
     * Parse Excel file dan return collection of data rows
     * 
     * @param Collection $collection
     * @return Collection
     */
    public function collection(Collection $collection)
    {
        return $collection;
    }
}
