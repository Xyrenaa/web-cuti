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

        // 1. Jika yang login adalah Admin
        if ($request->user()->hasAnyRole(['admin', 'Admin'])) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Jika yang login adalah Kepala
        if ($request->user()->hasRole('kepala')) {
            // Pastikan nama route-nya sesuai dengan yang ada di web.php milikmu
            return redirect()->route('kepala.dashboard'); 
        }

        // 3. Jika yang login adalah Pegawai (atau role default lainnya)
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
