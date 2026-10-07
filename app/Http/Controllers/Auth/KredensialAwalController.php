<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class KredensialAwalController extends Controller
{
    /**
     * Simpan email aktif + kata sandi baru dari pop-up login pertama.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Endpoint ini hanya untuk akun yang memang wajib. Kalau sudah selesai,
        // jangan beri jalan pintas ganti email/password tanpa password lama.
        if (! $user->perluGantiKredensial()) {
            return redirect()->route('dashboard');
        }

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validateWithBag('kredensialAwal', [
            'email' => [
                'required', 'string', 'email', 'max:255', 'confirmed',
                Rule::unique(User::class, 'email')->ignore($user->id),
                function ($attribute, $value, $fail) {
                    if (User::emailSementara($value)) {
                        $fail('Gunakan alamat email aktif milik Anda, bukan email sementara.');
                    }
                },
            ],
            'password' => [
                'required', 'confirmed', Password::defaults(),
                function ($attribute, $value, $fail) use ($user) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Kata sandi baru tidak boleh sama dengan kata sandi awal.');
                    }
                },
            ],
        ], [
            'email.confirmed'    => 'Konfirmasi email tidak sama.',
            'email.unique'       => 'Email ini sudah dipakai akun lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $user->forceFill([
            'email'                  => $data['email'],
            'password'               => Hash::make($data['password']),
            'wajib_ganti_kredensial' => false,
            'remember_token'         => Str::random(60),
        ])->save();

        // Akun ini tadinya berbagi password yang sama dengan semua pegawai:
        // tendang sesi lain milik akun ini (hanya jika session driver = database).
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return redirect()->route('dashboard')->with('kredensial_berhasil', true);
    }
}