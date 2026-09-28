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
        // Bersihkan NIP dari segala karakter selain angka SEBELUM divalidasi.
        // Ini yang bikin "anomali spasi" hilang total: berapa pun bentuk
        // spasi/format yang terkirim dari mask di form, yang sampai ke sini
        // dan tersimpan selalu 18 digit polos — sama persis konvensinya
        // dengan NIP hasil import Excel.
        $request->merge([
            'nip' => preg_replace('/\D/', '', (string) $request->input('nip')),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nip' => ['required', 'regex:/^\d{18}$/', 'unique:'.User::class],
            'bagian_bidang_id' => ['required', 'integer'],
            'sub_bagian_seksi_id' => ['required', 'integer'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nip.regex' => 'NIP harus terdiri dari tepat 18 digit angka, tidak boleh kurang atau lebih.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'status_kepegawaian' => User::statusKepegawaianDariNip($request->nip),
            'bagian_bidang_id' => $request->bagian_bidang_id,
            'sub_bagian_seksi_id' => $request->sub_bagian_seksi_id,
            'password' => $request->password,
        ]);
        $user->assignRole('Pegawai');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}