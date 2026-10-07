<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama pegawai belum konfirmasi email & ganti password awal, satu-satunya
 * yang boleh diakses: halaman dashboard (tempat pop-up tampil), endpoint
 * simpan kredensial, dan logout. Selain itu dialihkan ke dashboard, sehingga
 * pop-up tidak bisa dilewati dengan membuka URL lain / menembak POST langsung.
 */
class WajibGantiKredensial
{
    private const ROUTE_DIIZINKAN = [
        'dashboard',
        'admin.dashboard',
        'kredensial.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->perluGantiKredensial()) {
            return $next($request);
        }

        if ($request->routeIs(self::ROUTE_DIIZINKAN)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Konfirmasi email dan ganti kata sandi terlebih dahulu.',
            ], 423);
        }

        return redirect()->route('dashboard');
    }
}