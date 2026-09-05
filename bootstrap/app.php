<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // SUDAH DIPERBAIKI: Menggunakan 'Middleware' (tanpa 's')
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        // PERBAIKAN: project ini diakses lewat Cloudflare Tunnel (cloudflared)
        // yang jalan di komputer yang sama dan connect ke Laravel via
        // 127.0.0.1 -- tanpa ini, semua request (dari internet manapun)
        // kelihatan datang dari 127.0.0.1 di ActivityLog/request()->ip(),
        // karena Laravel default tidak percaya header X-Forwarded-For dari
        // siapapun (termasuk dari localhost sendiri). '127.0.0.1' di sini
        // artinya "cuma percaya header forward dari proxy yang connect dari
        // localhost" -- aman SELAMA Laravel tidak juga bisa diakses langsung
        // dari jaringan lain selain lewat cloudflared (default `php artisan
        // serve` sudah bind ke 127.0.0.1, bukan 0.0.0.0, jadi aman).
        $middleware->trustProxies(
            at: '127.0.0.1',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );
    })->create();