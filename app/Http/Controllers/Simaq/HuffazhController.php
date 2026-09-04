<?php

namespace App\Http\Controllers\Simaq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HuffazhController extends Controller
{
    public function index()
    {
        return view('simaq.huffazh.index');
    }

    public function presensiIndex()
    {
        return view('simaq.huffazh.presensi');
    }

    public function presensiStore(Request $request)
    {
        // Logika presensi 2x sepekan
        return redirect()->back()->with('success', 'Presensi Jam\'iyyatul Huffazh tersimpan.');
    }

    public function tasmiCreate($santri_id)
    {
        // Bisa me-return view yang sama dengan Tasmi reguler, 
        // tapi controller ini bisa mengirimkan penanda "is_huffazh"
        return view('simaq.tasmi.create', [
            'santri_id' => $santri_id,
            'is_huffazh' => true
        ]);
    }

    public function tasmiStore(Request $request)
    {
        // Sama dengan store tasmi biasa, tapi ditandai eksklusif untuk anak Huffazh
        return redirect()->route('simaq.huffazh.index')->with('success', 'Tasmi\' Huffazh tersimpan.');
    }
}