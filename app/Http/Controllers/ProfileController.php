<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
   */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // 1. Definisikan variabel $user di awal agar rapi
        $user = $request->user(); 

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->filled('cropped_avatar')) {
            // Ambil string Base64
            $image_parts = explode(";base64,", $request->cropped_avatar);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Beri nama unik
            $fileName = $user->id . '_avatar_' . time() . '.' . $image_type;
            
            // Simpan gambar ke folder storage/app/public/avatars
            \Illuminate\Support\Facades\Storage::disk('public')->put('avatars/' . $fileName, $image_base64);
            
            // Simpan nama file ke database
            $user->avatar = $fileName;
        }

        // Simpan semua perubahan ke database (sekarang $user sudah dikenali!)
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    
    // =========================================================
    // FUNGSI KHUSUS ADMIN
    // =========================================================

    /**
     * Menampilkan halaman profil admin.
     */
    public function showAdmin(Request $request)
    {
        return view('admin.profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Menampilkan form edit profil admin.
     */
    public function editAdmin(Request $request)
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }
}
