<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SantriExport;
use App\Http\Controllers\Controller;
use App\Imports\SantriKelasAsramaImport;
use App\Models\Asrama;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use App\Services\SantriImportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }

        // Perbaikan: view sudah punya dropdown filter "Kelas" dan "L/P" (jenis_kelamin),
        // tapi sebelumnya filter ini tidak pernah diterapkan ke query sama sekali,
        // dan $kelasList (buat isi dropdown-nya) juga tidak pernah dikirim ke view --
        // menyebabkan "Undefined variable $kelasList" saat halaman dibuka.
        if ($request->filled('kelas_id')) {
            $query->whereHas('santriKelas', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id)->where('status', 'aktif');
            });
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $santri = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        $ta = TahunAjaran::aktif();
        $kelasList = Kelas::when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->orderBy('nama')->get();

        return view('santri.index', compact('santri', 'kelasList'));
    }

    public function create()
    {
        $ta = TahunAjaran::aktif();
        $kelasList = Kelas::when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->orderBy('nama')->get();
        $kelasAktif = null;

        return view('santri.create', compact('kelasList', 'ta', 'kelasAktif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'           => ['required', 'string', 'unique:santri,nis'],
            'nisn'          => ['nullable', 'string', 'unique:santri,nisn'],
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'tempat_lahir'  => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat'        => ['nullable', 'string'],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['nullable', 'string', 'max:20'],
            'angkatan'      => ['required', 'integer'],
            'status'        => ['required', 'in:aktif,nonaktif,alumni'],
            'kelas_id'      => ['nullable', 'exists:kelas,id'],
        ]);

        $kelasId = $validated['kelas_id'] ?? null;
        unset($validated['kelas_id']);

        $santri = Santri::create($validated);
        ActivityLogService::logCreate($santri);

        // Simpan penempatan kelas kalau dipilih di form. Tahun ajaran diambil
        // dari TA aktif di server (bukan dari input tersembunyi di form) supaya
        // tidak bisa dimanipulasi lewat request.
        $ta = TahunAjaran::aktif();
        if ($kelasId && $ta) {
            SantriKelas::create([
                'santri_id'       => $santri->id,
                'kelas_id'        => $kelasId,
                'tahun_ajaran_id' => $ta->id,
                'status'          => 'aktif',
            ]);
        }

        return redirect()->route('admin.santri.show', $santri)
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function show(Santri $santri)
    {
        return view('santri.show', compact('santri'));
    }

    public function profil(Santri $santri)
    {
        return view('santri.profil', compact('santri'));
    }

    public function edit(Santri $santri)
    {
        $ta = TahunAjaran::aktif();
        $kelasList = Kelas::when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->orderBy('nama')->get();
        // kelasAktif() -> hasOneThrough ke model Kelas, PK-nya `id` (bukan kolom
        // `kelas_id` yang dulunya salah dipakai di _form.blade.php).
        $kelasAktif = $santri->kelasAktif;

        return view('santri.edit', compact('santri', 'kelasList', 'ta', 'kelasAktif'));
    }

    public function update(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nis'           => ['required', 'string', 'unique:santri,nis,' . $santri->id],
            'nisn'          => ['nullable', 'string', 'unique:santri,nisn,' . $santri->id],
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'tempat_lahir'  => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat'        => ['nullable', 'string'],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['nullable', 'string', 'max:20'],
            'angkatan'      => ['required', 'integer'],
            'status'        => ['required', 'in:aktif,nonaktif,alumni'],
            'kelas_id'      => ['nullable', 'exists:kelas,id'],
        ]);

        $kelasId = $validated['kelas_id'] ?? null;
        unset($validated['kelas_id']);

        $santri->update($validated);
        ActivityLogService::logUpdate($santri);

        $ta = TahunAjaran::aktif();
        if ($kelasId && $ta) {
            // 1 santri = 1 baris per tahun ajaran (unique santri_id+tahun_ajaran_id),
            // jadi update kalau baris untuk TA ini sudah ada, buat baru kalau belum.
            SantriKelas::updateOrCreate(
                ['santri_id' => $santri->id, 'tahun_ajaran_id' => $ta->id],
                ['kelas_id' => $kelasId, 'status' => 'aktif']
            );
        }

        return redirect()->route('admin.santri.show', $santri)
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        ActivityLogService::logDelete($santri);
        $santri->delete();

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'kelas_id']);

        return Excel::download(
            new SantriExport($filters),
            'data-santri-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Download template Excel kosong (header + 1 baris contoh) untuk fitur
     * import kelas & asrama massal.
     */
    public function importTemplate()
    {
        return Excel::download(
            new \App\Exports\SantriImportTemplateExport(),
            'template-import-santri.xlsx'
        );
    }

    /**
     * Tampilkan form untuk bulk import kelas & asrama
     */
    public function importBulk()
    {
        $ta = TahunAjaran::aktif();

        if (!$ta) {
            return redirect()->route('admin.santri.index')
                ->with('error', 'Belum ada Tahun Ajaran aktif. Aktifkan Tahun Ajaran dulu sebelum import kelas & asrama.');
        }

        // Kelas untuk TA aktif ini -- kalau kosong, import "Kelas" pasti gagal
        // semua karena tidak ada yang bisa dicocokkan. Kasih peringatan di awal
        // supaya admin bikin kelasnya dulu, bukan ketemu error satu-satu di preview.
        $jumlahKelasTaAktif = Kelas::where('tahun_ajaran_id', $ta->id)->count();

        return view('santri.import-bulk', compact('ta', 'jumlahKelasTaAktif'));
    }

    /**
     * Handle preview data sebelum save
     */
    public function previewBulk(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $ta = TahunAjaran::aktif();
        if (!$ta) {
            return back()->withErrors(['file' => 'Belum ada Tahun Ajaran aktif.']);
        }

        try {
            // Parse Excel file
            $rows = Excel::toCollection(
                new SantriKelasAsramaImport(),
                $request->file('file')
            )->flatten(1);

            // Convert Collection to array
            $rowsArray = $rows->map(function ($row) {
                return $row->toArray();
            })->toArray();

            // Validate dan prepare preview
            $importService = new SantriImportService($ta);
            $preview = $importService->validateAndPrepare($rowsArray);

            // Simpan preview data ke session untuk digunakan saat save
            $request->session()->put('santri_import_preview', $preview);

            return view('santri.import-preview', compact('preview', 'ta'));
        } catch (ValidationException $e) {
            return back()->withErrors(['file' => 'File Excel tidak valid: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error membaca file: ' . $e->getMessage()]);
        }
    }

    /**
     * Simpan data setelah preview dikonfirmasi
     */
    public function storeBulk(Request $request)
    {
        $preview = $request->session()->get('santri_import_preview');

        if (!$preview) {
            return redirect()->route('admin.santri.import-bulk')
                ->withErrors(['message' => 'Session preview telah expired, silakan upload ulang']);
        }

        // Parse approval dari form (row_index => 'approve' or 'reject' or 'skip')
        $approvals = [];
        foreach ($preview['records'] as $index => $record) {
            $approvals[$index] = $request->input("action_{$index}", 'approve');
        }

        try {
            $ta = TahunAjaran::aktif();
            $importService = new SantriImportService($ta);
            $results = $importService->save($approvals, $preview);

            // Clear session
            $request->session()->forget('santri_import_preview');

            return redirect()->route('admin.santri.index')
                ->with('success', "Bulk import selesai: {$results['success']} berhasil, {$results['skipped']} skip, {$results['failed']} gagal")
                ->with('messages', $results['messages']);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error saat menyimpan data: ' . $e->getMessage()]);
        }
    }
}
