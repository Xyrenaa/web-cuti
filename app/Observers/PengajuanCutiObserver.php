<?php

namespace App\Observers;

use App\Models\PengajuanCuti;
use App\Models\User;
use App\Notifications\StatusCutiNotification;

class PengajuanCutiObserver
{
    /**
     * Step di mana Admin Kepegawaian butuh BERTINDAK.
     * 3 = Menunggu Verifikasi Admin, 7 = Menunggu Persetujuan Final dari Admin
     * (Disamakan dengan query di PengajuanController::dashboardAdmin())
     */
    protected array $verifikasiSteps = [3, 7];

    /**
     * Step 10 = "Dibatalkan". Satu-satunya tempat yang set ke step ini
     * adalah PengajuanController::batal() (aksi pegawai membatalkan
     * pengajuannya sendiri). Ini notifikasi INFORMATIF, bukan minta aksi.
     */
    protected int $batalStep = 10;

    /**
     * Dipanggil saat pengajuan baru dibuat (misal: pegawai jalur TU
     * yang langsung masuk ke step 3 lewat PengajuanController::store()).
     */
    public function created(PengajuanCuti $pengajuan): void
    {
        $this->notifyAdminIfNeeded($pengajuan);
    }

    /**
     * Dipanggil saat pengajuan diupdate — approve Kepala (approval_step
     * berpindah ke 3/7) ATAU pegawai membatalkan pengajuannya (ke step 10).
     */
    public function updated(PengajuanCuti $pengajuan): void
    {
        if ($pengajuan->wasChanged('approval_step')) {
            $this->notifyAdminIfNeeded($pengajuan);
        }
    }

    protected function notifyAdminIfNeeded(PengajuanCuti $pengajuan): void
    {
        $step = $pengajuan->approval_step;
        $namaPegawai = $pengajuan->user->name ?? 'Pegawai';

        if (in_array($step, $this->verifikasiSteps)) {
            if ($step === 3) {
                $judul = 'Verifikasi Cuti Diperlukan';
                $pesan = "Pengajuan cuti {$namaPegawai} ({$pengajuan->kode_pengajuan}) menunggu verifikasi Anda.";
            } else {
                $judul = 'Finalisasi Cuti Diperlukan';
                $pesan = "Pengajuan cuti {$namaPegawai} ({$pengajuan->kode_pengajuan}) menunggu persetujuan final Anda.";
            }
        } elseif ($step === $this->batalStep) {
            $judul = 'Pengajuan Cuti Dibatalkan';
            $pesan = "{$namaPegawai} membatalkan pengajuan cuti ({$pengajuan->kode_pengajuan}). Tidak perlu tindakan lebih lanjut.";
        } else {
            return;
        }

        // Sesuaikan nama role dengan RoleSeeder: 'Admin Kepegawaian'
        $admins = User::role('Admin Kepegawaian')->get();

        foreach ($admins as $admin) {
            $admin->notify(new StatusCutiNotification($judul, $pesan));
        }
    }
}