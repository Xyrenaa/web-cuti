<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nip' => ['required', 'string', 'max:50', 'unique:'.User::class], // Validasi NIP
            'divisi' => ['required', 'string', 'max:255'],                     // Validasi Divisi
            'jabatan' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,         // Simpan NIP
            'divisi' => $request->divisi,   // Simpan Divisi
            'jabatan' => $request->jabatan,
            'password' => Hash::make($request->password),
        ]);

       // LOGIKA DETEKSI JABATAN PINTAR 
        $jabatan = strtolower($request->jabatan);

        if (str_contains($jabatan, 'admin') || str_contains($jabatan, 'kepegawaian')) {
            $user->assignRole('Admin Kepegawaian');
        } elseif (str_contains($jabatan, 'kepala') || str_contains($jabatan, 'kasi') || str_contains($jabatan, 'kabid') || str_contains($jabatan, 'kasubag')) {
            if (str_contains($jabatan, 'seksi') || str_contains($jabatan, 'kasi')) {
                $user->assignRole('Kepala Seksi');
            } elseif (str_contains($jabatan, 'bidang') || str_contains($jabatan, 'kabid')) {
                $user->assignRole('Kepala Bidang');
            } elseif (str_contains($jabatan, 'sub') || str_contains($jabatan, 'kasubag')) {
                $user->assignRole('Kepala Sub Bagian');
            } elseif (str_contains($jabatan, 'tu') || str_contains($jabatan, 'tata usaha')) {
                $user->assignRole('Kepala TU');
            } elseif (str_contains($jabatan, 'kantor') || str_contains($jabatan, 'kakan')) {
                $user->assignRole('Kepala Kantor');
            } else {
                $user->assignRole('Kepala Seksi'); 
            }
        } else {
            $user->assignRole('Pegawai');
        }
        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
