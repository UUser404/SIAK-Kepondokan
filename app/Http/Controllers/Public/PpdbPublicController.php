<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\PpdbController;
use App\Models\PpdbPendaftar;
use App\Models\PpdbPeriode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PpdbPublicController extends Controller
{
    /**
     * Landing page PPDB publik
     */
    public function index()
    {
        $periode = PpdbPeriode::where('is_active', true)
            ->where('tanggal_buka', '<=', today())
            ->where('tanggal_tutup', '>=', today())
            ->first();

        $isOpen = $periode !== null;

        $stats = $periode ? [
            'total_daftar' => PpdbPendaftar::where('ppdb_periode_id', $periode->id)->count(),
            'sisa_kuota'   => $periode->kuota - PpdbPendaftar::where('ppdb_periode_id', $periode->id)
                                ->where('status', 'diterima')->count(),
        ] : [];

        return view('public.ppdb-landing', compact('periode', 'isOpen', 'stats'));
    }

    /**
     * Form pendaftaran
     */
    public function create()
    {
        $periode = PpdbPeriode::where('is_active', true)
            ->where('tanggal_buka', '<=', today())
            ->where('tanggal_tutup', '>=', today())
            ->first();

        abort_if(!$periode, 404, 'PPDB sedang tidak dibuka.');

        return view('public.ppdb-form', compact('periode'));
    }

    /**
     * Simpan pendaftaran
     */
    public function store(Request $request)
    {
        $periode = PpdbPeriode::where('is_active', true)
            ->where('tanggal_buka', '<=', today())
            ->where('tanggal_tutup', '>=', today())
            ->firstOrFail();

        $validated = $request->validate([
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'tempat_lahir'  => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'nisn'          => ['nullable', 'string', 'digits:10'],
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['required', 'string', 'max:20'],
            'email_wali'    => ['nullable', 'email', 'max:100'],
            'alamat'        => ['nullable', 'string', 'max:500'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'no_hp_wali.required'   => 'No. HP wali wajib diisi.',
            'nisn.digits'           => 'NISN harus 10 digit.',
        ]);

        $nomorDaftar = PpdbController::generateNomorDaftar($periode);

        $pendaftar = PpdbPendaftar::create(array_merge($validated, [
            'ppdb_periode_id' => $periode->id,
            'nomor_daftar'    => $nomorDaftar,
            'status'          => 'menunggu',
        ]));

        return redirect()->route('ppdb.public.cek', $nomorDaftar)
            ->with('success', "Pendaftaran berhasil! Nomor daftar Anda: {$nomorDaftar}");
    }

    /**
     * Cek status pendaftaran
     */
    public function cekStatus(string $nomor_daftar)
    {
        $pendaftar = PpdbPendaftar::where('nomor_daftar', $nomor_daftar)
            ->with('ppdbPeriode')
            ->first();

        return view('public.ppdb-status', compact('pendaftar', 'nomor_daftar'));
    }
}
