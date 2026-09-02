<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

/**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = $request->user();

    // 1. Cek jika user adalah Admin
    if ($user->hasRole('Admin')) {
        return redirect()->route('admin.dashboard'); // Sesuaikan dengan nama route admin kamu
    } 
    
    // 2. Cek jika user adalah Kepala/Kabag
    elseif ($user->hasAnyRole(['Kepala Seksi', 'Kepala Bidang', 'Kepala Sub Bagian', 'Kepala TU', 'Kepala Kantor'])) {
        return redirect()->route('dashboard.kepala'); // Sesuaikan dengan nama route kabag kamu
    }

    // 3. Jika bukan keduanya (berarti Pegawai biasa)
    return redirect()->intended(route('dashboard', absolute: false));
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
