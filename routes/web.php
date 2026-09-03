<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;
use App\Models\SubBagianSeksi;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $parakepala = [
        'Kepala Seksi',
        'Kepala Bagian',
        'Kepala Bidang',
        'Kepala Sub-Bagian',
        'Kepala TU',
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

Route::get('/api/sub-bagian/{bagian_id}', function ($bagian_id) {
    return App\Models\SubBagianSeksi::where('bagian_bidang_id', $bagian_id)->get();
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

// Rute Notifikasi Khusus Admin
Route::get('/admin/notifikasi', [App\Http\Controllers\PengajuanController::class, 'notifikasiAdmin'])->name('admin.notifikasi');

// Rute untuk MELIHAT Profil Admin
Route::get('/admin/profile', [ProfileController::class, 'showAdmin'])->name('admin.profile.show');

// Rute untuk MENGEDIT Profil Admin
Route::get('/admin/profile/edit', [ProfileController::class, 'editAdmin'])->name('admin.profile.edit');

// TAMBAHKAN RUTE INI UNTUK TOMBOL AKSI VERIFIKASI ADMIN
Route::post('/admin/approval/{id}/verifikasi', [PengajuanController::class, 'verifikasiAdmin'])->name('admin.approval.verifikasi');

// Rute untuk halaman Rekap Cuti
Route::get('/admin/rekap', [App\Http\Controllers\PengajuanController::class, 'rekapAdmin'])->name('admin.rekap.index');

// Rute untuk aksi Ekspor Excel (Nanti disambungkan ke fungsi ekspor sungguhan)
Route::get('/admin/rekap/export', [App\Http\Controllers\PengajuanController::class, 'exportRekap'])->name('admin.rekap.export');

// Rute untuk melihat detail rekap 1 pegawai
Route::get('/admin/rekap/{id}', [App\Http\Controllers\PengajuanController::class, 'showRekap'])->name('admin.rekap.show');

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
