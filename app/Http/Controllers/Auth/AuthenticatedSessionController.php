<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Captcha;
use Illuminate\Http\JsonResponse;
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
        // Setiap halaman login dibuka (termasuk setelah salah isi) = kode baru.
        // Kode lama di session otomatis tergantikan.
        return view('auth.login', [
            'captchaSrc' => Captcha::aktif() ? Captcha::segarkan() : null,
        ]);
    }

/**
     * Handle an incoming authentication request.
     */
/**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // 1. Jika yang login adalah Admin (Disesuaikan dengan nama role di sistem)
        if ($request->user()->hasAnyRole(['Admin Kepegawaian', 'admin', 'Admin'])) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Cek Para Kepala (Ditambahkan 'Kepala Bidang')
        if ($request->user()->hasAnyRole(['Kepala Seksi', 'Kepala Sub-Bagian', 'Kepala Bagian', 'Kepala Kantor', 'Kepala Bidang'])) {
            return redirect()->route('dashboard'); // Sesuaikan jika ada route dashboard khusus kepala
        }

        // 3. Jika yang login adalah Pegawai (atau role default lainnya)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Tombol "ganti gambar" di halaman login: buat kode baru (yang lama hangus)
     * dan kirim gambarnya sebagai JSON. Jawaban tidak pernah ikut dikirim.
     */
    public function segarkanCaptcha(): JsonResponse
    {
        abort_unless(Captcha::aktif(), 404);

        return response()
            ->json(['src' => Captcha::segarkan()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
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