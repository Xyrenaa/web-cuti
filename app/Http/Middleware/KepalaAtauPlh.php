<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepalaAtauPlh
{
    public const ROLE_KEPALA = [
        'Kepala Seksi',
        'Kepala Bidang',
        'Kepala Sub-Bagian',
        'Kepala TU',
        'Kepala Kantor',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole(self::ROLE_KEPALA) || $user->sedangMenjadiPlh()) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman approval.');
    }
}