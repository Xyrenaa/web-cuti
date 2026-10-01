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
     *  - tanpaPlh      : kepala sedang/akan cuti (<= 7 hari) tapi belum ada Plh
     *  - penggantiCuti : pengganti yang sedang menjabat ternyata sedang cuti hari ini
     */
    public function perhatian(): array
    {
        $hariIni = now()->toDateString();
        $batas   = now()->addDays(7)->toDateString();

        $kepalas = $this->daftarKepala();

        $cutiKepala = PengajuanCuti::where('approval_step', 8)
            ->whereIn('user_id', $kepalas->pluck('id'))
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->whereDate('tanggal_mulai', '<=', $batas)
            ->orderBy('tanggal_mulai')
            ->get()
            ->groupBy('user_id');

        $tanpaPlh = [];
        foreach ($kepalas as $kepala) {
            $cuti = $cutiKepala->get($kepala->id)?->first();
            if (! $cuti || ! ($jabatan = $kepala->jabatanKepala())) {
                continue;
            }

            $sudahAda = PenugasanPejabat::statusAktif()
                ->untukJabatan(...$jabatan)
                ->whereDate('tanggal_mulai', '<=', Carbon::parse($cuti->tanggal_selesai)->toDateString())
                ->where(function ($w) use ($cuti) {
                    $w->whereNull('tanggal_selesai')
                      ->orWhereDate('tanggal_selesai', '>=', Carbon::parse($cuti->tanggal_mulai)->toDateString());
                })
                ->exists();

            if (! $sudahAda) {
                $tanpaPlh[] = ['kepala' => $kepala, 'label' => $this->labelJabatan($kepala), 'cuti' => $cuti];
            }
        }

        $penggantiCuti = [];
        $berlaku = PenugasanPejabat::berlaku()->with(['pengganti', 'bagianBidang', 'subBagianSeksi'])->get();
        foreach ($berlaku as $p) {
            $cuti = $this->cutiBentrok($p->pengganti_id, $hariIni)->first();
            if ($cuti) {
                $penggantiCuti[] = ['penugasan' => $p, 'cuti' => $cuti];
            }
        }

        return compact('tanpaPlh', 'penggantiCuti');
    }
}