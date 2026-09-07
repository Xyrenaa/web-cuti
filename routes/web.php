<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;
use App\Models\SubBagianSeksi;
use App\Http\Controllers\DashboardKepalaController;

Route::get('/', function () {
    return redirect()->route('login');
});

// UPDATE: Perbaikan di Route Dashboard
Route::get('/dashboard', function () {
    $parakepala = [
        'Kepala Seksi',
        'Kepala Bagian',
        'Kepala Bidang',
        'Kepala Sub-Bagian',
        'Kepala TU',
        'Kepala Kantor'
    ];
    
    // Jika role adalah salah satu dari Kepala
    if (auth()->user()->hasAnyRole($parakepala)){
        // Alihkan eksekusinya ke DashboardKepalaController untuk mengambil semua data EIS
        return app(\App\Http\Controllers\DashboardKepalaController::class)->index();
    }
    
    // Jika bukan Kepala (Pegawai biasa)
    return view('pegawai.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// ==========================================
// RUTE PEGAWAI
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
});
Route::get('/riwayat-pengajuan', [App\Http\Controllers\PengajuanController::class, 'riwayat'])->name('pengajuan.riwayat');
Route::get('/pengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'show'])->name('pengajuan.show');
Route::get('/pengajuan/detail/{id}', [\App\Http\Controllers\PengajuanController::class, 'show'])->name('pegawai.detail');
Route::post('/pengajuan/{id}/batal', [App\Http\Controllers\PengajuanController::class, 'batal'])->name('pengajuan.batal');
Route::get('/notifikasi', [App\Http\Controllers\PengajuanController::class, 'notifikasi'])->name('notifikasi');
Route::post('/notifikasi/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markAsRead'])->name('notifikasi.read');
Route::post('/notifikasi/read-all', [\App\Http\Controllers\NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.readAll');

Route::get('/api/sub-bagian/{bagian_id}', function ($bagian_id) {
    return App\Models\SubBagianSeksi::where('bagian_bidang_id', $bagian_id)->get();
});


// ==========================================
// RUTE ADMIN
// ==========================================
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');
// Route Admin yang baru
// 1. Dashboard Admin

Route::get('/admin/dashboard', [App\Http\Controllers\PengajuanController::class, 'dashboardAdmin'])->middleware(['auth'])->name('admin.dashboard');

// Route::get('/admin/dashboard', function () {
    // return view('admin.dashboard');
// })->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/admin/approval', [\App\Http\Controllers\PengajuanController::class, 'indexApproval'])->name('admin.approval.index');
Route::get('/admin/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showApproval'])->name('admin.approval.show');
Route::get('/admin/notifikasi', [App\Http\Controllers\PengajuanController::class, 'notifikasiAdmin'])->name('admin.notifikasi');
Route::get('/admin/profile', [ProfileController::class, 'showAdmin'])->name('admin.profile.show');
Route::get('/admin/profile/edit', [ProfileController::class, 'editAdmin'])->name('admin.profile.edit');
Route::post('/admin/approval/{id}/verifikasi', [PengajuanController::class, 'verifikasiAdmin'])->name('admin.approval.verifikasi');
Route::get('/admin/rekap', [App\Http\Controllers\PengajuanController::class, 'rekapAdmin'])->name('admin.rekap.index');
Route::get('/admin/rekap/export', [App\Http\Controllers\PengajuanController::class, 'exportRekap'])->name('admin.rekap.export');
Route::get('/admin/rekap/{id}', [App\Http\Controllers\PengajuanController::class, 'showRekap'])->name('admin.rekap.show');


// ==========================================
// RUTE KEPALA (Approval)
// ==========================================
Route::middleware(['auth', 'verified'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/approval', [\App\Http\Controllers\PengajuanController::class, 'indexKepala'])->name('approval.index');
    Route::get('/approval/{id}', [\App\Http\Controllers\PengajuanController::class, 'showKepala'])->name('approval.show');
    Route::put('/approval/{id}/approve', [\App\Http\Controllers\PengajuanController::class, 'approveKepala'])->name('approval.approve');
    Route::put('/approval/{id}/reject', [\App\Http\Controllers\PengajuanController::class, 'tolakKepala'])->name('approval.reject');
    Route::put('/approval/{id}/revisi', [\App\Http\Controllers\PengajuanController::class, 'revisiKepala'])->name('approval.revisi'); 
});


// ==========================================
// RUTE PROFIL BAWAAN BREEZE
// ==========================================
Route::get('/profil', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
Route::get('/profil/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profil', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profil', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

require __DIR__.'/auth.php';