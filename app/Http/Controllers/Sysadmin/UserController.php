<?php
// ============================================================
// app/Http/Controllers/Sysadmin/UserController.php
// ============================================================
namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Perbaikan: "Manajemen User" ini khusus buat 6 role staff
        // (mudir, wakil_kurikulum, guru, kesantrian, admin, sysadmin).
        // Akun portal santri (dibuat otomatis dari PpdbController::konversiKeSantri())
        // sengaja DIKECUALIKAN di sini -- jumlahnya bisa ribuan (1 per santri),
        // jadi akan menenggelamkan daftar staff yang cuma segelintir kalau ikut
        // ditampilkan. Akun portal santri dikelola dari halaman Profil Santri
        // masing-masing (admin.santri.show), bukan dari sini.
        $query = User::where('role', '!=', 'santri');

        if ($request->filled('search')) {
            $query->where(
                fn($q) =>
                $q->where('name',  'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
            );
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'nama_arab' => ['nullable', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', Rule::in(['mudir', 'wakil_kurikulum', 'guru', 'kesantrian', 'admin', 'sysadmin'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'nama_arab' => $request->nama_arab,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);
        ActivityLogService::logCreate($user);

        return redirect()->route('sysadmin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'nama_arab' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['mudir', 'wakil_kurikulum', 'guru', 'kesantrian', 'admin', 'sysadmin'])],
        ]);

        $old = $user->toArray();
        $user->update(['name' => $request->name, 'nama_arab' => $request->nama_arab, 'email' => $request->email, 'role' => $request->role]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8']]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);
        ActivityLogService::logUpdate($user, $old);

        return redirect()->route('sysadmin.users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function toggleAktif(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        ActivityLogService::log($user->is_active ? 'user.activated' : 'user.deactivated', $user);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function resetPassword(User $user)
    {
        $defaultPwd = 'password';
        $user->update(['password' => Hash::make($defaultPwd)]);
        ActivityLogService::log('user.password_reset', $user);

        return back()->with('success', "Password {$user->name} direset ke: {$defaultPwd}");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
        $user->update(['is_active' => false]);
        ActivityLogService::logDelete($user);

        return back()->with('success', 'User dinonaktifkan.');
    }
}
