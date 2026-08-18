<?php
// ============================================================
// app/Http/Controllers/Guru/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\NilaiAkhir;
use App\Models\PenugasanMengajar;
use App\Models\Pertemuan;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        // Kelas & mapel yang ditugaskan Kurikulum ke guru ini
        $penugasanList = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['mataPelajaran', 'kelas'])
            ->get();

        // Tandai kelas-mapel yang sudah/belum diinput presensinya HARI INI
        $penugasanList->each(function ($penugasan) {
            $penugasan->sudahPresensi = Pertemuan::where('guru_id', $penugasan->guru_id)
                ->where('kelas_id', $penugasan->kelas_id)
                ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
                ->whereDate('tanggal', today())
                ->exists();
        });

        // Yang BELUM presensi ditaruh duluan -- sebelumnya urutan cuma
        // ngikutin default database, jadi kalau guru punya >6 kombinasi
        // (dashboard cuma nampilin 6 lewat take(6) di view), yang sudah
        // beres bisa saja nutupin yang justru masih perlu dikerjakan.
        $penugasanList = $penugasanList->sortBy('sudahPresensi')->values();

        $totalMapel = $penugasanList->pluck('mata_pelajaran_id')->unique()->count();
        $totalKelas = $penugasanList->pluck('kelas_id')->unique()->count();

        $pertemuanBulanIni = Pertemuan::where('guru_id', $user->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        // Nilai pending = jumlah santri (across semua kombinasi kelas+mapel
        // yang diampu guru ini) yang BELUM punya NilaiAkhir buat TA ini.
        // SEBELUMNYA cuma hardcode 0 -- selalu "0 Nilai Pending" apapun
        // kondisi sebenarnya, bukan cuma kurang informatif tapi aktif
        // menyesatkan. Pola loop-per-penugasan ini SENGAJA konsisten
        // dengan cara sudahPresensi dihitung di atas (N+1 query, bukan 1
        // query gabungan) -- dashboard guru jumlah penugasannya kecil
        // (biasanya belasan, bukan ratusan), jadi trade-off ini wajar
        // untuk keterbacaan kode, bukan dioptimasi prematur.
        $nilaiPending = 0;
        foreach ($penugasanList as $penugasan) {
            $totalSantri = SantriKelas::where('kelas_id', $penugasan->kelas_id)
                ->where('status', 'aktif')
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->count();

            $sudahDinilai = NilaiAkhir::where('kelas_id', $penugasan->kelas_id)
                ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->count();

            $nilaiPending += max(0, $totalSantri - $sudahDinilai);
        }

        // Kartu pengingat Wali Kelas -- SEBELUMNYA tidak ada jejak sama
        // sekali di dashboard utama guru, padahal kalau dia kebetulan juga
        // wali kelas, gampang kelupaan cek tugas itu kalau tidak notice
        // sendiri section "Wali Kelas" di sidebar. Query & filter tahun
        // ajaran SENGAJA sama persis dengan yang dipakai di
        // sidebar-nav.blade.php (guru-sidebar-section) supaya jumlah kelas
        // yang kehitung konsisten di kedua tempat -- jangan diubah salah
        // satu tanpa ubah yang lain.
        $kelasWaliList = $user->isWaliKelas()
            ? $user->waliKelasKelas()
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('tingkatan')
            ->withCount('santri as jumlah_santri')
            ->get()
            : collect();

        return view('dashboards.guru', compact(
            'penugasanList',
            'totalMapel',
            'totalKelas',
            'pertemuanBulanIni',
            'nilaiPending',
            'kelasWaliList'
        ));
    }
}
