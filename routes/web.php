<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
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
    return view('pegawai.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Pegawai
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
});


// Route Admin yang baru
// 1. Dashboard Admin
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

// Rute untuk melihat tabel daftar approval
Route::get('/admin/approval', [\App\Http\Controllers\PengajuanController::class, 'indexApproval'])->name('admin.approval.index');

// Rute untuk melihat halaman detail approval (yang ada trackingnya)
Route::get('/admin/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showApproval'])->name('admin.approval.show');

// Rute Khusus Approval Kepala
Route::middleware(['auth', 'verified'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/approval', [\App\Http\Controllers\PengajuanController::class, 'indexKepala'])->name('approval.index');
    Route::get('/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showKepala'])->name('approval.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
