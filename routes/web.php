<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $parakepala = [
        'Kepala Seksi',
        'Kepala Bidang',
        'Kepala Sub Bagian',
        'Kepala Tata Usaha',
        'Kepala Kantor'
    ];
    if (auth()->user()->hasAnyRole($parakepala)){
        return view('dashboard-kepala');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route Admin yang baru
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
