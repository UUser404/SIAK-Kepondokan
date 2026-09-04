<?php

namespace App\Http\Controllers\Simaq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TasmiController extends Controller
{
    public function index()
    {
        return view('simaq.tasmi.index');
    }

    public function create($santri_id)
    {
        return view('simaq.tasmi.create', compact('santri_id'));
    }

    public function store(Request $request)
    {
        // 1. Tangkap input "Juz Berapa" dan total kesalahan
        // 2. Lempar ke SimaqScoringService dengan parameter jenis_ujian = tasmi
        
        return redirect()->route('simaq.tasmi.index')->with('success', 'Imtihan Tasmi\' 1 Juz berhasil dicatat.');
    }
}