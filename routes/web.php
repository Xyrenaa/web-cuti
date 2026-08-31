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
        'Kepala Bagian',
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

// Rute untuk melihat tabel daftar approval
Route::get('/admin/approval', [\App\Http\Controllers\PengajuanController::class, 'indexApproval'])->name('admin.approval.index');

// Rute untuk melihat halaman detail approval (yang ada trackingnya)
Route::get('/admin/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showApproval'])->name('admin.approval.show');

// Rute Khusus Approval Kepala
Route::middleware(['auth', 'verified'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/approval', [\App\Http\Controllers\PengajuanController::class, 'indexKepala'])->name('approval.index');
    Route::get('/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showKepala'])->name('approval.show');
    // Rute Aksi Approval
    Route::put('/approval/{id}/approve', [\App\Http\Controllers\PengajuanController::class, 'approveKepala'])->name('approval.approve');
    Route::put('/approval/{id}/reject', [\App\Http\Controllers\PengajuanController::class, 'tolakKepala'])->name('approval.reject');
    Route::put('/approval/{id}/revisi', [\App\Http\Controllers\PengajuanController::class, 'revisiKepala'])->name('approval.revisi'); // INI RUTE BARUNYA
});



    Route::get('/profil', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Route bawaan breeze (sesuaikan path-nya jika mau diubah ke bahasa indonesia)
    Route::get('/profil/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');


require __DIR__.'/auth.php';
