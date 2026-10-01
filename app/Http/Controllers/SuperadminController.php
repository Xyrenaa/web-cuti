<?php

namespace App\Http\Controllers;

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
            'plh_aktif'       => PenugasanPejabat::berlaku()->where('jenis', PenugasanPejabat::JENIS_PLH)->count(),
            'plt_aktif'       => PenugasanPejabat::berlaku()->where('jenis', PenugasanPejabat::JENIS_PLT)->count(),
            'perlu_perhatian' => count($perhatian['tanpaPlh']) + count($perhatian['penggantiCuti']),
        ];

        return view('superadmin.dashboard', compact('stat'));
    }
}