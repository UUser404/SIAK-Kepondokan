<?php
// ============================================================
// app/Http/Controllers/Admin/TemplateSuratController.php
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateSuratController extends Controller
{
    public function index()
    {
        $templates = TemplateSurat::orderBy('nama')->paginate(15);
        return view('template-surat.index', compact('templates'));
    }

    public function create()
    {
        // Placeholder yang tersedia untuk panduan pengguna
        $placeholders = $this->getPlaceholders();
        return view('template-surat.create', compact('placeholders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'   => ['required', 'string', 'max:100'],
            'kode'   => ['required', 'string', 'max:30', 'unique:template_surat,kode'],
            'konten' => ['required', 'string'],
        ]);

        $template = TemplateSurat::create([
            'nama'      => $request->nama,
            'kode'      => strtoupper($request->kode),
            'konten'    => $request->konten,
            'is_active' => true,
        ]);

        ActivityLogService::logCreate($template);

        return redirect()->route('admin.template-surat.index')
            ->with('success', 'Template surat berhasil dibuat.');
    }

    public function show(TemplateSurat $templateSurat)
    {
        return view('template-surat.show', compact('templateSurat'));
    }

    public function edit(TemplateSurat $templateSurat)
    {
        $placeholders = $this->getPlaceholders();
        return view('template-surat.edit', compact('templateSurat', 'placeholders'));
    }

    public function update(Request $request, TemplateSurat $templateSurat)
    {
        $request->validate([
            'nama'   => ['required', 'string', 'max:100'],
            'kode'   => [
                'required',
                'string',
                'max:30',
                Rule::unique('template_surat', 'kode')->ignore($templateSurat->id)
            ],
            'konten' => ['required', 'string'],
        ]);

        $old = $templateSurat->toArray();
        $templateSurat->update([
            'nama'   => $request->nama,
            'kode'   => strtoupper($request->kode),
            'konten' => $request->konten,
        ]);
        ActivityLogService::logUpdate($templateSurat, $old);

        return redirect()->route('admin.template-surat.index')
            ->with('success', 'Template surat diperbarui.');
    }

    public function destroy(TemplateSurat $templateSurat)
    {
        $templateSurat->update(['is_active' => false]);
        return back()->with('success', 'Template dinonaktifkan.');
    }

    private function getPlaceholders(): array
    {
        return [
            ['{{nomor_surat}}',      'Nomor surat otomatis'],
            ['{{tanggal_surat}}',    'Tanggal surat (format: D MMMM Y)'],
            ['{{perihal}}',          'Perihal surat'],
            ['{{ditujukan_kepada}}', 'Nama/instansi tujuan'],
            ['{{nama_santri}}',      'Nama lengkap santri'],
            ['{{nis}}',              'NIS santri'],
            ['{{kelas}}',            'Kelas aktif santri'],
            ['{{nama_wali}}',        'Nama wali santri'],
            ['{{no_hp_wali}}',       'No. HP wali'],
            ['{{nama_pondok}}',      config('siak.pondok.nama')],
            ['{{kepala_pondok}}',    'Nama mudir pondok'],
            ['{{tahun}}',            now()->year],
            ['{{bulan}}',            now()->locale('id')->isoFormat('MMMM')],
        ];
    }
}
