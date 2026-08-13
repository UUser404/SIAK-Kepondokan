<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export leger nilai ke Excel. Pakai FromView (bukan FromCollection) supaya
 * bisa reuse struktur tabel yang sama dengan tampilan web/PDF -- 1 sumber
 * kebenaran untuk urutan kolom, bukan dikelola 3x terpisah (web, PDF, excel).
 *
 * View-nya (leger-nilai.export-excel) SENGAJA terpisah dari leger-nilai.show
 * -- versi excel tidak boleh ada elemen non-tabel (tombol, navigasi, dsb),
 * dan strukturnya harus tabel HTML polos (<table><tr><td>) supaya
 * Maatwebsite\Excel bisa parse jadi cell dengan benar.
 */
class LegerNilaiExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(
        private Kelas $kelas,
        private array $data,
        private TahunAjaran $ta,
    ) {}

    public function view(): \Illuminate\Contracts\View\View
    {
        return view('leger-nilai.export-excel', [
            'kelas' => $this->kelas,
            'data'  => $this->data,
            'ta'    => $this->ta,
        ]);
    }

    public function title(): string
    {
        // Nama sheet Excel max 31 karakter, tidak boleh ada karakter tertentu
        // (/ \ ? * [ ]) -- nama kelas seharusnya aman, tapi dipotong jaga-jaga.
        return substr('Leger ' . $this->kelas->nama, 0, 31);
    }
}
