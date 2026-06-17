<?php
// ============================================================
// app/Http/Controllers/Guru/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Pertemuan;
use App\Models\Nilai;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();
        $hari = strtolower(now()->locale('id')->dayName);

        // Jadwal hari ini
        $jadwalHariIniList = JadwalPelajaran::where('guru_id', $user->id)
            ->where('hari', $hari)
            ->with(['mataPelajaran', 'kelas'])
            ->orderBy('jam_mulai')
            ->get();

        // Tandai sudah/belum presensi
        $jadwalHariIniList->each(function ($jadwal) {
            $jadwal->sudahPresensi = Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)
                ->whereDate('tanggal', today())
                ->exists();
        });

        $jadwalHariIni = $jadwalHariIniList->count();

        // Total kelas yang diajar
        $totalKelas = JadwalPelajaran::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->whereHas('kelas', fn($k) => $k->where('tahun_ajaran_id', $ta->id)))
            ->distinct('kelas_id')
            ->count('kelas_id');

        // Jadwal belum diisi presensi (minggu ini)
        $belumPresensi = JadwalPelajaran::where('guru_id', $user->id)
            ->whereIn('hari', $this->hariMingguIni())
            ->get()
            ->filter(function ($jadwal) {
                $tanggalJadwal = $this->getTanggalHariIni($jadwal->hari);
                if ($tanggalJadwal->isFuture()) return false;
                return !Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)
                    ->whereDate('tanggal', $tanggalJadwal)
                    ->exists();
            })->count();

        // Nilai yang belum diinput (santri di kelas tanpa nilai UTS/UAS)
        $nilaiPending = 0; // Kalkulasi ringan untuk dashboard

        return view('dashboards.guru', compact(
            'jadwalHariIniList',
            'jadwalHariIni',
            'totalKelas',
            'belumPresensi',
            'nilaiPending'
        ));
    }

    private function hariMingguIni(): array
    {
        $map = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu'
        ];
        $hari = [];
        for ($i = 0; $i <= now()->dayOfWeek - 1; $i++) {
            $nama = now()->startOfWeek()->addDays($i)->format('l');
            if (isset($map[$nama])) $hari[] = $map[$nama];
        }
        return $hari;
    }

    private function getTanggalHariIni(string $hari): \Carbon\Carbon
    {
        $map = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6];
        $target = $map[$hari] ?? 1;
        return now()->startOfWeek()->addDays($target - 1);
    }
}
