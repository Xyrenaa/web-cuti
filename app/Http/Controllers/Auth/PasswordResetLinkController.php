<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['nip' => User::bersihkanNip($request->input('nip'))]);

        $request->validate([
            'nip' => ['required', 'string'],
        ], [
            'nip.required' => 'NIP wajib diisi.',
        ]);

        $user = User::where('nip', $request->nip)->first();

        if (! $user) {
            return back()->withInput($request->only('nip'))
                ->withErrors(['nip' => 'NIP tidak terdaftar.']);
        }

        // Akun yang belum konfirmasi email hanya punya email sementara yang tidak
        // bisa menerima surel. Arahkan ke jalur yang benar daripada "terkirim" palsu.
        if ($user->wajib_ganti_kredensial || User::emailSementara($user->email)) {
            return back()->withInput($request->only('nip'))->withErrors([
                'nip' => 'Akun ini belum mengonfirmasi email. Login dengan kata sandi awal untuk mengisi email & mengganti kata sandi, atau hubungi Admin Kepegawaian.',
            ]);
        }

        // Broker reset password Laravel bekerja per email, jadi NIP diterjemahkan
        // ke email akun tersebut. Tautan dikirim ke email tersimpan, bukan ke input user.
        $status = Password::sendResetLink(['email' => $user->email]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', 'Tautan atur ulang kata sandi telah dikirim ke ' . $user->emailTersamar() . '.')
                    : back()->withInput($request->only('nip'))
                        ->withErrors(['nip' => __($status)]);
    }
}