<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureGuruSimaq Middleware
 * 
 * Middleware untuk memastikan user memiliki akses ke modul SIMAQ.
 * Digunakan pada routes yang memerlukan permission SIMAQ.
 */
class EnsureGuruSimaq
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user belum authenticated, redirect ke login
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login terlebih dahulu');
        }

        $user = auth()->user();

        // Super admin/admin selalu boleh
        if ($user->hasRole(['admin', 'super_admin'])) {
            return $next($request);
        }

        // User harus punya permission manage_simaq atau view_simaq
        if ($user->hasPermissionTo('manage_simaq') || $user->hasPermissionTo('view_simaq')) {
            return $next($request);
        }

        // Jika tidak punya akses, return 403 Forbidden
        abort(403, 'Akses SIMAQ terbatas untuk guru tahsin/tahfizh yang terdaftar');
    }
}
