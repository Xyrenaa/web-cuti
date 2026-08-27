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
Route::get('/riwayat-pengajuan', [App\Http\Controllers\PengajuanController::class, 'riwayat'])->name('pengajuan.riwayat');
Route::get('/pengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'show'])->name('pengajuan.show');
Route::get('/notifikasi', [App\Http\Controllers\PengajuanController::class, 'notifikasi'])->name('notifikasi');
Route::post('/notifikasi/tandai-dibaca', [App\Http\Controllers\PengajuanController::class, 'tandaiSemuaDibaca'])->name('notifikasi.read');


// Route Admin yang baru
// 1. Dashboard Admin
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

// 2. Halaman Daftar Approval Cuti
Route::get('/admin/approval', function () {
    return view('admin.approval.index'); 
})->middleware(['auth', 'verified'])->name('admin.approval');

// 3. Halaman Detail Approval Cuti
Route::get('/admin/approval/{id}', function ($id) {
    return view('admin.approval.show'); 
})->middleware(['auth', 'verified'])->name('admin.approval.show');



    Route::get('/profil', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Route bawaan breeze (sesuaikan path-nya jika mau diubah ke bahasa indonesia)
    Route::get('/profil/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');


require __DIR__.'/auth.php';
