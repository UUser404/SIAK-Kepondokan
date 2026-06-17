<?php
// ============================================================
// app/Http/Controllers/Admin/PendidikController.php
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PendidikRequest;
use App\Models\TenagaPendidik;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PendidikController extends Controller
{
    public function index(Request $request)
    {
        $query = TenagaPendidik::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->status_kepegawaian);
        }

        if ($request->filled('role')) {
            $query->whereHas('user', fn($q) => $q->where('role', $request->role));
        }

        $pendidik = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pendidik.index', compact('pendidik'));
    }

    public function create()
    {
        return view('pendidik.create');
    }

    public function store(PendidikRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password ?? 'password'),
                'role'      => $request->role,
                'is_active' => true,
            ]);
            $user->assignRole($request->role);

            $foto = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto')->store('pendidik/foto', 'public');
            }

            $pendidik = TenagaPendidik::create([
                ...$request->validated(),
                'user_id' => $user->id,
                'foto'    => $foto,
            ]);

            ActivityLogService::logCreate($pendidik);
        });

        return redirect()->route('admin.pendidik.index')
            ->with('success', 'Data tenaga pendidik berhasil ditambahkan.');
    }

    public function show(TenagaPendidik $pendidik)
    {
        $pendidik->load(['user', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas']);

        return view('pendidik.show', compact('pendidik'));
    }

    public function edit(TenagaPendidik $pendidik)
    {
        $pendidik->load('user');
        return view('pendidik.edit', compact('pendidik'));
    }

    public function update(PendidikRequest $request, TenagaPendidik $pendidik)
    {
        $oldValues = $pendidik->toArray();

        DB::transaction(function () use ($request, $pendidik) {
            if ($request->hasFile('foto')) {
                if ($pendidik->foto) Storage::disk('public')->delete($pendidik->foto);
                $pendidik->foto = $request->file('foto')->store('pendidik/foto', 'public');
            }

            $pendidik->update($request->validated());

            $pendidik->user->update([
                'name'  => $request->name,
                'email' => $request->email,
                'role'  => $request->role,
            ]);

            if ($request->filled('password')) {
                $pendidik->user->update(['password' => Hash::make($request->password)]);
            }

            // Sync role Spatie
            $pendidik->user->syncRoles([$request->role]);

            ActivityLogService::logUpdate($pendidik, $oldValues);
        });

        return redirect()->route('admin.pendidik.show', $pendidik)
            ->with('success', 'Data tenaga pendidik berhasil diperbarui.');
    }

    public function destroy(TenagaPendidik $pendidik)
    {
        $pendidik->user->update(['is_active' => false]);
        ActivityLogService::logDelete($pendidik);

        return redirect()->route('admin.pendidik.index')
            ->with('success', 'Akun tenaga pendidik berhasil dinonaktifkan.');
    }
}