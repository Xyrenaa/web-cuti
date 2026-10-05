<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use App\Models\PenugasanPejabat;
use App\Models\User;
use App\Services\PenugasanPejabatService;

class SuperadminController extends Controller
{
    public function dashboard(PenugasanPejabatService $service)
    {
        $perhatian = $service->perhatian();

        $stat = [
            'total_pegawai'   => User::bukanSuperadmin()->count(),
            // PLH pilihan kepala yang sedang bertugas hari ini
            'plh_aktif'       => PengajuanCuti::plhAktifPada()->count(),
            // PLT dari Superadmin yang sedang berlaku
            'plt_aktif'       => PenugasanPejabat::berlaku()->where('jenis', PenugasanPejabat::JENIS_PLT)->count(),
            'perlu_perhatian' => count($perhatian['tanpaPlh'])
                               + count($perhatian['plhBerhalangan'])
                               + count($perhatian['pltBerhalangan']),
        ];

        return view('superadmin.dashboard', compact('stat'));
    }
}