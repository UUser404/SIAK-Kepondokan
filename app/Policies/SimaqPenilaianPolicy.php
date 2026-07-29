<?php

namespace App\Policies;

use App\Models\SimaqPenilaian;
use App\Models\User;

/**
 * SimaqPenilaianPolicy
 * 
 * Policy untuk kontrol akses SimaqPenilaian model.
 * 
 * Aturan:
 * - Global Read: Semua user dengan permission 'view_any_simaq_penilaian' bisa view semua
 * - Scoped Write: User hanya bisa update/delete penilaian yang guru_id-nya milik user itu
 * - Super Admin: Bypass semua aturan
 */
class SimaqPenilaianPolicy
{
    /**
     * Determine whether the user can view any model.
     * 
     * Global read: semua guru_simaq bisa view semua penilaian
     */
    public function viewAny(User $user): bool
    {
        // Super admin / admin - boleh
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        // User dengan permission view_any_simaq_penilaian - boleh
        if ($user->hasPermissionTo('view_any_simaq_penilaian')) {
            return true;
        }

        // User dengan permission manage_simaq (full access) - boleh
        if ($user->hasPermissionTo('manage_simaq')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Global read: jika user bisa viewAny, dia bisa view individual record
     */
    public function view(User $user, SimaqPenilaian $penilaian): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super admin - boleh
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        // User dengan permission create_simaq_penilaian atau manage_simaq - boleh
        if ($user->hasPermissionTo('create_simaq_penilaian') || $user->hasPermissionTo('manage_simaq')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     * 
     * Scoped write: hanya guru yang membuat penilaian (guru_id match) atau super admin
     */
    public function update(User $user, SimaqPenilaian $penilaian): bool
    {
        // Super admin - boleh update apapun
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        // User hanya boleh update penilaian yang dia buat (guru_id match)
        if ($user->tenagaPendidik && $user->tenagaPendidik->id === $penilaian->guru_id) {
            return $user->hasPermissionTo('update_simaq_penilaian');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * Scoped write: hanya guru yang membuat penilaian (guru_id match) atau super admin
     */
    public function delete(User $user, SimaqPenilaian $penilaian): bool
    {
        // Super admin - boleh delete apapun
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        // User hanya boleh delete penilaian yang dia buat (guru_id match)
        if ($user->tenagaPendidik && $user->tenagaPendidik->id === $penilaian->guru_id) {
            return $user->hasPermissionTo('delete_simaq_penilaian');
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model (dari soft delete).
     */
    public function restore(User $user, SimaqPenilaian $penilaian): bool
    {
        // Sama dengan delete logic
        return $this->delete($user, $penilaian);
    }

    /**
     * Determine whether the user can permanently delete the model (force delete).
     * 
     * Hanya super admin yang boleh force delete
     */
    public function forceDelete(User $user, SimaqPenilaian $penilaian): bool
    {
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }
}
