<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\NotifikasiController;
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


// ==========================================
// ROUTE PEGAWAI (Semua wajib login)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
    
    Route::get('/riwayat-pengajuan', [PengajuanController::class, 'riwayat'])->name('pengajuan.riwayat');
    Route::get('/pengajuan/{id}', [PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/detail/{id}', [PengajuanController::class, 'show'])->name('pegawai.detail');
    Route::post('/pengajuan/{id}/batal', [PengajuanController::class, 'batal'])->name('pengajuan.batal');
    
    Route::get('/notifikasi', [PengajuanController::class, 'notifikasi'])->name('notifikasi');
    Route::post('/notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.readAll');
    
    // Route Informasi Cuti untuk Pegawai (URL: /informasi-cuti)
    Route::get('/informasi-cuti', [PengajuanController::class, 'informasi'])->name('pegawai.informasi');
});

// Route API bebas akses
Route::get('/api/sub-bagian/{bagian_id}', function ($bagian_id) {
    return SubBagianSeksi::where('bagian_bidang_id', $bagian_id)->get();
});


// ==========================================
// ROUTE ADMIN (Wajib login)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/approval', [PengajuanController::class, 'indexApproval'])->name('admin.approval.index');
    Route::get('/admin/approval/{id}', [PengajuanController::class, 'showApproval'])->name('admin.approval.show');
    Route::post('/admin/approval/{id}/verifikasi', [PengajuanController::class, 'verifikasiAdmin'])->name('admin.approval.verifikasi');

    Route::get('/admin/notifikasi', [PengajuanController::class, 'notifikasiAdmin'])->name('admin.notifikasi');

    Route::get('/admin/profile', [ProfileController::class, 'showAdmin'])->name('admin.profile.show');
    Route::get('/admin/profile/edit', [ProfileController::class, 'editAdmin'])->name('admin.profile.edit');

    Route::get('/admin/rekap', [PengajuanController::class, 'rekapAdmin'])->name('admin.rekap.index');
    Route::get('/admin/rekap/export', [PengajuanController::class, 'exportRekap'])->name('admin.rekap.export');
    Route::get('/admin/rekap/{id}', [PengajuanController::class, 'showRekap'])->name('admin.rekap.show');
});


// ==========================================
// ROUTE KEPALA (Prefix: /kepala, Name: kepala.)
// ==========================================
Route::middleware(['auth', 'verified'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/approval', [PengajuanController::class, 'indexKepala'])->name('approval.index');
    Route::get('/approval/{id}', [PengajuanController::class, 'showKepala'])->name('approval.show');
    Route::put('/approval/{id}/approve', [PengajuanController::class, 'approveKepala'])->name('approval.approve');
    Route::put('/approval/{id}/reject', [PengajuanController::class, 'tolakKepala'])->name('approval.reject');
    Route::put('/approval/{id}/revisi', [PengajuanController::class, 'revisiKepala'])->name('approval.revisi');
    
    // Route Informasi Cuti untuk Kepala (URL: /kepala/informasi-cuti)
    // Otomatis bernama 'kepala.informasi' karena berada di dalam group name('kepala.')
    Route::get('/informasi-cuti', [PengajuanController::class, 'informasi'])->name('informasi');
});


// ==========================================
// ROUTE PROFIL BAWAAN BREEZE
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';