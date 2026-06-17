<?php
// ============================================================
// app/Http/Controllers/Admin/SuratController.php
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Models\TemplateSurat;
use App\Models\Santri;
use App\Services\ActivityLogService;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SuratController extends Controller
{
    public function __construct(private SuratService $suratService) {}

    public function index(Request $request)
    {
        $query = SuratKeluar::with(['santri', 'dibuatOleh'])
            ->orderByDesc('tanggal_surat');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_surat', 'like', "%{$request->search}%")
                    ->orWhere('perihal', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_surat', $request->bulan);
        }

        $suratList = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => SuratKeluar::count(),
            'draft'    => SuratKeluar::where('status', 'draft')->count(),
            'terbit'   => SuratKeluar::where('status', 'diterbitkan')->count(),
            'bulan_ini' => SuratKeluar::whereMonth('tanggal_surat', now()->month)
                ->whereYear('tanggal_surat', now()->year)->count(),
        ];

        return view('surat.index', compact('suratList', 'stats'));
    }

    public function create(Request $request)
    {
        $templates  = TemplateSurat::where('is_active', true)->orderBy('nama')->get();
        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();
        $template   = $request->template_id
            ? TemplateSurat::find($request->template_id)
            : null;

        return view('surat.create', compact('templates', 'santriList', 'template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_surat_id' => ['nullable', 'exists:template_surat,id'],
            'perihal'           => ['required', 'string', 'max:200'],
            'ditujukan_kepada'  => ['nullable', 'string', 'max:200'],
            'santri_id'         => ['nullable', 'exists:santri,id'],
            'tanggal_surat'     => ['required', 'date'],
            'konten'            => ['required', 'string'],
        ]);

        $nomor = $this->suratService->generateNomor();

        $surat = SuratKeluar::create([
            'template_surat_id' => $request->template_surat_id,
            'nomor_surat'       => $nomor,
            'perihal'           => $request->perihal,
            'ditujukan_kepada'  => $request->ditujukan_kepada,
            'santri_id'         => $request->santri_id,
            'tanggal_surat'     => $request->tanggal_surat,
            'konten'            => $request->konten,
            'status'            => 'draft',
            'dibuat_oleh'       => Auth::id(),
        ]);

        ActivityLogService::logCreate($surat);

        return redirect()->route('admin.surat.show', $surat)
            ->with('success', "Surat berhasil dibuat. Nomor: {$nomor}");
    }

    public function show(SuratKeluar $surat)
    {
        $surat->load(['santri', 'dibuatOleh', 'templateSurat']);

        // Render konten dengan placeholder
        $data = $this->suratService->buildDataFromSurat($surat);
        $kontenRendered = $this->suratService->renderTemplate($surat->konten, $data);

        return view('surat.show', compact('surat', 'kontenRendered'));
    }

    public function edit(SuratKeluar $surat)
    {
        abort_if($surat->status === 'diterbitkan', 403, 'Surat yang sudah diterbitkan tidak dapat diedit.');

        $templates  = TemplateSurat::where('is_active', true)->orderBy('nama')->get();
        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();

        return view('surat.edit', compact('surat', 'templates', 'santriList'));
    }

    public function update(Request $request, SuratKeluar $surat)
    {
        abort_if($surat->status === 'diterbitkan', 403);

        $request->validate([
            'perihal'          => ['required', 'string', 'max:200'],
            'ditujukan_kepada' => ['nullable', 'string', 'max:200'],
            'santri_id'        => ['nullable', 'exists:santri,id'],
            'tanggal_surat'    => ['required', 'date'],
            'konten'           => ['required', 'string'],
        ]);

        $old = $surat->toArray();
        $surat->update($request->only('perihal', 'ditujukan_kepada', 'santri_id', 'tanggal_surat', 'konten'));
        ActivityLogService::logUpdate($surat, $old);

        return redirect()->route('admin.surat.show', $surat)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $surat)
    {
        abort_if($surat->status === 'diterbitkan', 403, 'Surat diterbitkan tidak dapat dihapus.');

        ActivityLogService::logDelete($surat);
        $surat->delete();

        return redirect()->route('admin.surat.index')
            ->with('success', 'Surat berhasil dihapus.');
    }

    public function terbitkan(SuratKeluar $surat)
    {
        abort_if($surat->status === 'diterbitkan', 422, 'Surat sudah diterbitkan.');

        $surat->update(['status' => 'diterbitkan']);
        ActivityLogService::log('surat.diterbitkan', $surat);

        return back()->with('success', "Surat {$surat->nomor_surat} berhasil diterbitkan.");
    }

    public function cetak(SuratKeluar $surat)
    {
        $data = $this->suratService->buildDataFromSurat($surat);
        $kontenRendered = $this->suratService->renderTemplate($surat->konten, $data);

        $pdf = Pdf::loadView('surat.cetak-pdf', compact('surat', 'kontenRendered'))
            ->setPaper('a4', 'portrait');

        $filename = 'surat-' . str_replace('/', '-', $surat->nomor_surat) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * AJAX: Ambil konten template + render dengan data santri
     */
    public function getKontenTemplate(Request $request)
    {
        $template = TemplateSurat::findOrFail($request->template_id);
        $data     = ['nomor_surat' => $this->suratService->generateNomor()];

        if ($request->filled('santri_id')) {
            $santri = Santri::with(['santriKelas.kelas'])->find($request->santri_id);
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

        $konten = $this->suratService->renderTemplate($template->konten, $data);

        return response()->json([
            'konten'  => $konten,
            'perihal' => $template->nama,
        ]);
    }
}
