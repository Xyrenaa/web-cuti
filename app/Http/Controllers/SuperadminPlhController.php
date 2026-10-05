<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use App\Models\PlhPergantian;
use App\Models\User;
use App\Notifications\StatusCutiNotification;
use App\Services\PenugasanPejabatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Superadmin mengelola PLH yang ditunjuk KEPALA pada pengajuan cutinya sendiri.
 * Superadmin hanya menukar bila PLH pilihan kepala ternyata berhalangan.
 */
class SuperadminPlhController extends Controller
{
    public function __construct(private PenugasanPejabatService $service)
    {
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['selesai', 'penukaran']) ? $request->query('tab') : 'berjalan';

        if ($tab === 'penukaran') {
            $log = PlhPergantian::with(['pengajuan.user', 'lama', 'baru', 'pelaku'])
                ->latest()->paginate(10)->onEachSide(1)->withQueryString();

            return view('superadmin.plh.index', ['tab' => $tab, 'pengajuans' => null, 'log' => $log, 'perhatian' => null]);
        }

        $hariIni = today()->toDateString();

        // Cuti kepala yang sudah sampai tahap penunjukan PLH (step 7 = menunggu final Admin, 8 = disetujui).
        $pengajuans = PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi', 'plh'])
            ->whereIn('approval_step', [7, 8])
            ->whereHas('user', fn ($u) => $u->role(PengajuanCuti::ROLE_WAJIB_PLH))
            ->when(
                $tab === 'berjalan',
                fn ($q) => $q->whereDate('tanggal_selesai', '>=', $hariIni)->orderBy('tanggal_mulai'),
                fn ($q) => $q->whereDate('tanggal_selesai', '<', $hariIni)->orderByDesc('tanggal_mulai')
            )
            ->paginate(10)->onEachSide(1)->withQueryString();

        $pengajuans->getCollection()->each(function ($p) {
            $p->status_plh   = $this->statusPlh($p);
            $p->plh_bentrok  = ($p->plh_user_id && $p->status_plh !== 'selesai')
                ? $this->service->cutiBentrok($p->plh_user_id, $p->tanggal_mulai, $p->tanggal_selesai)->first()
                : null;
        });

        return view('superadmin.plh.index', [
            'tab'        => $tab,
            'pengajuans' => $pengajuans,
            'log'        => null,
            'perhatian'  => $tab === 'berjalan' ? $this->service->perhatian() : null,
        ]);
    }

    public function tukarForm($id)
    {
        $pengajuan = $this->ambilPengajuan($id);
        $bentrok   = $this->pegawaiBentrok($pengajuan);
        $rek       = $pengajuan->kandidatPlh()->pluck('id')->all(); // yang langsung di bawah kepala

        $opsi = User::bisaJadiPengganti()
            ->where('id', '!=', $pengajuan->user_id)
            ->when($pengajuan->plh_user_id, fn ($q, $plhId) => $q->where('id', '!=', $plhId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => [
                'id'       => $u->id,
                'nama'     => $u->name,
                'rek'      => in_array($u->id, $rek),
                'bentrok'  => $bentrok[$u->id] ?? null,
                'tersedia' => ! isset($bentrok[$u->id]),
            ])
            ->sortBy([['tersedia', 'desc'], ['rek', 'desc'], ['nama', 'asc']])
            ->values();

        return view('superadmin.plh.tukar', compact('pengajuan', 'opsi'));
    }

    public function tukar(Request $request, $id)
    {
        $pengajuan = $this->ambilPengajuan($id);

        $data = $request->validate([
            'plh_user_id' => ['required', 'exists:users,id'],
            'alasan'      => ['required', 'string', 'max:500'],
        ], [
            'plh_user_id.required' => 'Pilih pegawai yang akan menjadi PLH.',
            'alasan.required'      => 'Alasan wajib diisi (tercatat di riwayat).',
        ]);

        // bisaJadiPengganti() sudah menyingkirkan Superadmin dan Admin Kepegawaian.
        $baru = User::bisaJadiPengganti()->find($data['plh_user_id']);

        if (! $baru) {
            return back()->withInput()->withErrors(['plh_user_id' => 'Pegawai ini tidak dapat ditunjuk sebagai PLH.']);
        }
        if ($baru->id === $pengajuan->user_id) {
            return back()->withInput()->withErrors(['plh_user_id' => 'Kepala yang cuti tidak bisa menjadi PLH-nya sendiri.']);
        }
        if ((int) $baru->id === (int) $pengajuan->plh_user_id) {
            return back()->withInput()->withErrors(['plh_user_id' => 'Pegawai ini sudah menjadi PLH saat ini.']);
        }

        $bentrok = $this->pegawaiBentrok($pengajuan);
        if (isset($bentrok[$baru->id])) {
            return back()->withInput()->withErrors([
                'plh_user_id' => "{$baru->name} berhalangan ({$bentrok[$baru->id]}). Pilih pegawai lain.",
            ]);
        }

        $kepala = $pengajuan->user;
        $lama   = $pengajuan->plh_user_id ? User::find($pengajuan->plh_user_id) : null;

        DB::transaction(function () use ($pengajuan, $baru, $lama, $data, $request) {
            // PENTING: buang relasi 'plh' yang sudah ter-cache. Observer memakai $pengajuan->plh untuk
            // notifikasi; tanpa ini yang dikabari justru PLH LAMA.
            $pengajuan->unsetRelation('plh');
            $pengajuan->plh_user_id = $baru->id;
            $pengajuan->save(); // observer: PLH baru otomatis dinotifikasi

            PlhPergantian::create([
                'pengajuan_cuti_id' => $pengajuan->id,
                'plh_lama_id'       => $lama?->id,
                'plh_baru_id'       => $baru->id,
                'alasan'            => $data['alasan'],
                'dilakukan_oleh'    => $request->user()->id,
            ]);
        });

        $periode = Carbon::parse($pengajuan->tanggal_mulai)->translatedFormat('d F Y')
            . ' s.d. ' . Carbon::parse($pengajuan->tanggal_selesai)->translatedFormat('d F Y');
        $meta = [
            'tipe'           => 'status',
            'kode_pengajuan' => $pengajuan->kode_pengajuan,
            'pengajuan_id'   => $pengajuan->id,
            'url'            => route('dashboard'),
        ];

        $lama?->notify(new StatusCutiNotification(
            'Tugas PLH Anda Dialihkan',
            "Superadmin mengalihkan tugas PLH Anda menggantikan {$kepala->name} ({$periode}) kepada {$baru->name}. Alasan: {$data['alasan']}",
            $meta
        ));

        $kepala->notify(new StatusCutiNotification(
            $lama ? 'PLH Anda Diganti' : 'PLH Anda Ditetapkan',
            ($lama ? "Superadmin mengganti PLH Anda selama cuti ({$periode}) dari {$lama->name} menjadi {$baru->name}."
                   : "Superadmin menetapkan {$baru->name} sebagai PLH Anda selama cuti ({$periode}).")
                . " Alasan: {$data['alasan']}",
            $meta
        ));

        Log::info('Superadmin menukar PLH kepala', [
            'superadmin_id' => $request->user()->id,
            'pengajuan_id'  => $pengajuan->id,
            'plh_lama_id'   => $lama?->id,
            'plh_baru_id'   => $baru->id,
        ]);

        return redirect()->route('superadmin.plh.index')
            ->with('success', "{$baru->name} sekarang menjadi PLH {$kepala->name}. Riwayat penukaran tercatat.");
    }

    // ------------------------------------------------------------------

    /** Hanya pengajuan kepala yang wajib PLH, sudah di step 7/8, dan masa cutinya belum berakhir. */
    private function ambilPengajuan($id): PengajuanCuti
    {
        $pengajuan = PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi', 'plh'])->findOrFail($id);

        abort_unless(
            $pengajuan->butuhPlh()
                && in_array((int) $pengajuan->approval_step, [7, 8], true)
                && Carbon::parse($pengajuan->tanggal_selesai)->gte(today()),
            404
        );

        return $pengajuan;
    }

    private function statusPlh(PengajuanCuti $p): string
    {
        if ((int) $p->approval_step === 7) {
            return $p->plh_user_id ? 'menunggu_final' : 'belum_ditunjuk';
        }

        $mulai   = Carbon::parse($p->tanggal_mulai)->startOfDay();
        $selesai = Carbon::parse($p->tanggal_selesai)->endOfDay();

        if (now()->lt($mulai))   return 'terjadwal';
        if (now()->gt($selesai)) return 'selesai';

        return 'aktif';
    }

    /**
     * Pegawai yang TIDAK bisa jadi PLH pada periode cuti ini => [user_id => alasan].
     * Aturannya sama dengan PengajuanCuti::kandidatPlh() supaya konsisten.
     */
    private function pegawaiBentrok(PengajuanCuti $p): array
    {
        $mulai   = Carbon::parse($p->tanggal_mulai)->toDateString();
        $selesai = Carbon::parse($p->tanggal_selesai)->toDateString();
        $periode = fn ($q) => $q->whereDate('tanggal_mulai', '<=', $selesai)->whereDate('tanggal_selesai', '>=', $mulai);

        $sedangCuti = PengajuanCuti::whereNotIn('approval_step', [0, 9, 10])
            ->where('id', '!=', $p->id)->tap($periode)->pluck('user_id')->unique();

        $plhLain = PengajuanCuti::whereIn('approval_step', [7, 8])
            ->whereNotNull('plh_user_id')->where('id', '!=', $p->id)->tap($periode)->pluck('plh_user_id')->unique();

        $hasil = [];
        foreach ($sedangCuti as $uid) { $hasil[$uid] = 'memiliki cuti pada periode ini'; }
        foreach ($plhLain as $uid)    { $hasil[$uid] ??= 'sudah menjadi PLH kepala lain pada periode ini'; }

        return $hasil;
    }
}