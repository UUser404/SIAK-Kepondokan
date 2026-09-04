<?php

namespace App\Http\Controllers\Simaq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PemantapanController extends Controller
{
    public function index()
    {
        return view('simaq.pemantapan.index');
    }

    public function create($santri_id)
    {
        // Siapkan logika untuk memunculkan 10 baris soal sambung ayat
        return view('simaq.pemantapan.create', compact('santri_id'));
    }

    public function store(Request $request)
    {
        // 1. Tangkap array dari 10 soal
        // 2. Jumlahkan total kesalahan
        // 3. Lempar ke SimaqScoringService
        
        return redirect()->route('simaq.pemantapan.index')->with('success', 'Ujian Pemantapan berhasil disimpan.');
    }
}