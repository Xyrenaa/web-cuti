<?php

namespace App\Services;

use App\Models\PengajuanCuti;
use App\Models\PenugasanPejabat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PenugasanPejabatService
{
    /** Semua akun kepala yang jabatannya bisa digantikan (Kepala Kantor tidak termasuk). */
    public function daftarKepala(): Collection
    {
        return User::bukanSuperadmin()
            ->role(User::ROLE_BISA_DIGANTIKAN)
            ->with(['roles', 'bagianBidang', 'subBagianSeksi'])
            ->orderBy('name')
            ->get();
    }

    public function labelJabatan(User $kepala): string
    {
        $role = $kepala->jabatanKepala()[0] ?? '-';
        $unit = $kepala->subBagianSeksi?->nama ?? $kepala->bagianBidang?->nama;

        return $unit ? "{$role} — {$unit}" : $role;
    }

    /** Akun kepala yang memegang jabatan dari sebuah penugasan (dipakai untuk rekomendasi pada Plt). */
    public function kepalaDariJabatan(PenugasanPejabat $p): ?User
    {
        return User::bukanSuperadmin()
            ->role($p->jabatan_role)
            ->where('bagian_bidang_id', $p->bagian_bidang_id)
            ->where('sub_bagian_seksi_id', $p->sub_bagian_seksi_id)
            ->first();
    }

    /**
     * Calon pengganti yang LANGSUNG berada di bawah kepala tersebut:
     * - Kasi / Kasubag  -> staf di seksi / sub-bagian yang sama
     * - Kabid           -> para Kepala Seksi di bidangnya
     * - Kepala TU       -> para Kepala Sub-Bagian di TU
     */
    public function rekomendasiPengganti(User $kepala): Collection
    {
        $jabatan = $kepala->jabatanKepala();
        if (! $jabatan) {
            return collect();
        }

        [$role, $bagianId, $subId] = $jabatan;
        $dasar = User::bukanSuperadmin()->where('id', '!=', $kepala->id);

        return match ($role) {
            'Kepala Seksi', 'Kepala Sub-Bagian' => $dasar
                ->where('sub_bagian_seksi_id', $subId)
                ->whereDoesntHave('roles', fn ($r) => $r->whereIn('name', User::ROLE_KEPALA))
                ->get(),
            'Kepala Bidang' => $dasar->where('bagian_bidang_id', $bagianId)->role('Kepala Seksi')->get(),
            'Kepala TU'     => $dasar->where('bagian_bidang_id', $bagianId)->role('Kepala Sub-Bagian')->get(),
            default         => collect(),
        };
    }

    /** Cuti yang sudah final disetujui (step 8) dan beririsan dengan periode. */
    public function cutiBentrok($userIds, $mulai, $selesai = null): Collection
    {
        $mulai   = Carbon::parse($mulai)->toDateString();
        $selesai = Carbon::parse($selesai ?? $mulai)->toDateString();

        return PengajuanCuti::with('user')
            ->where('approval_step', 8)
            ->whereIn('user_id', (array) $userIds)
            ->whereDate('tanggal_mulai', '<=', $selesai)
            ->whereDate('tanggal_selesai', '>=', $mulai)
            ->orderBy('tanggal_mulai')
            ->get();
    }

    /** Apakah jabatan ini sudah punya penugasan aktif yang periodenya beririsan? */
    public function penugasanBentrok(array $jabatan, $mulai, $selesai = null): bool
    {
        $mulai = Carbon::parse($mulai)->toDateString();

        return PenugasanPejabat::statusAktif()
            ->untukJabatan(...$jabatan)
            ->where(function ($w) use ($mulai) {
                $w->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', $mulai);
            })
            ->when($selesai, fn ($q) => $q->whereDate('tanggal_mulai', '<=', Carbon::parse($selesai)->toDateString()))
            ->exists();
    }

    /**
     * Hal yang perlu ditindak superadmin:
     *  - tanpaPlh       : cuti kepala (step 7/8) yang sedang/akan berjalan (<= 7 hari) tapi PLH belum ditunjuk
     *  - plhBerhalangan : PLH pilihan kepala yang punya cuti bertabrakan dengan masa PLH-nya
     *  - pltBerhalangan : pengganti Plt/penugasan Superadmin yang sedang berlaku tapi sedang cuti hari ini
     */
    public function perhatian(): array
    {
        $hariIni = now()->toDateString();
        $batas   = now()->addDays(7)->toDateString();

        $kepalaCuti = PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi', 'plh'])
            ->whereIn('approval_step', [7, 8])
            ->whereHas('user', fn ($u) => $u->role(PengajuanCuti::ROLE_WAJIB_PLH))
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->whereDate('tanggal_mulai', '<=', $batas)
            ->orderBy('tanggal_mulai')
            ->get();

        $tanpaPlh = $kepalaCuti->whereNull('plh_user_id')
            ->map(fn ($p) => [
                'pengajuan' => $p,
                'kepala'    => $p->user,
                'label'     => $this->labelJabatan($p->user),
            ])
            ->values()->all();

        $plhBerhalangan = [];
        foreach ($kepalaCuti->whereNotNull('plh_user_id') as $p) {
            $cuti = $this->cutiBentrok($p->plh_user_id, $p->tanggal_mulai, $p->tanggal_selesai)->first();
            if ($cuti) {
                $plhBerhalangan[] = ['pengajuan' => $p, 'plh' => $p->plh, 'cuti' => $cuti];
            }
        }

        $pltBerhalangan = [];
        $berlaku = PenugasanPejabat::berlaku()->with(['pengganti', 'bagianBidang', 'subBagianSeksi'])->get();
        foreach ($berlaku as $t) {
            $cuti = $this->cutiBentrok($t->pengganti_id, $hariIni)->first();
            if ($cuti) {
                $pltBerhalangan[] = ['penugasan' => $t, 'cuti' => $cuti];
            }
        }

        return compact('tanpaPlh', 'plhBerhalangan', 'pltBerhalangan');
    }
}