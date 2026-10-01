<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use App\Models\PenugasanPejabat;
use App\Models\User;
use App\Services\PenugasanPejabatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SuperadminPenugasanController extends Controller
{
    public function __construct(private PenugasanPejabatService $service)
    {
    }

    public function index(Request $request)
    {
        $tab     = $request->query('tab') === 'riwayat' ? 'riwayat' : 'berlaku';
        $hariIni = now()->toDateString();

        $query = PenugasanPejabat::with(['pengganti', 'pejabatDefinitif', 'bagianBidang', 'subBagianSeksi']);

        if ($tab === 'berlaku') {
            $query->statusAktif()
                ->where(fn ($w) => $w->whereNull('tanggal_selesai')->orWhereDate('tanggal_selesai', '>=', $hariIni))
                ->orderBy('tanggal_mulai');
        } else {
            $query->where(fn ($w) => $w->where('status', '!=', PenugasanPejabat::STATUS_AKTIF)
                    ->orWhereDate('tanggal_selesai', '<', $hariIni))
                ->latest('id');
        }

        $penugasans = $query->paginate(10)->onEachSide(1)->withQueryString();
        $perhatian  = $tab === 'berlaku' ? $this->service->perhatian() : null;

        return view('superadmin.penugasan.index', compact('penugasans', 'perhatian', 'tab'));
    }

    public function create(Request $request)
    {
        $kepalas  = $this->service->daftarKepala();
        $pegawais = User::bukanSuperadmin()->orderBy('name')->get(['id', 'name', 'nip']);

        $opsiKepala = $kepalas->map(fn ($k) => [
            'id'    => $k->id,
            'label' => $this->service->labelJabatan($k) . ' — ' . $k->name,
        ])->values();

        $rekomendasi = $kepalas->mapWithKeys(fn ($k) => [
            $k->id => $this->service->rekomendasiPengganti($k)->pluck('id')->values(),
        ]);

        // Cuti terdekat tiap kepala, untuk mengisi tanggal Plh otomatis.
        $cuti = PengajuanCuti::where('approval_step', 8)
            ->whereIn('user_id', $kepalas->pluck('id'))
            ->whereDate('tanggal_selesai', '>=', now()->toDateString())
            ->orderBy('tanggal_mulai')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($g) => [
                'mulai'   => Carbon::parse($g->first()->tanggal_mulai)->toDateString(),
                'selesai' => Carbon::parse($g->first()->tanggal_selesai)->toDateString(),
            ]);

        return view('superadmin.penugasan.create', compact('opsiKepala', 'pegawais', 'rekomendasi', 'cuti'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis'           => ['required', Rule::in([PenugasanPejabat::JENIS_PLH, PenugasanPejabat::JENIS_PLT])],
            'kepala_id'       => ['required', 'exists:users,id'],
            'pengganti_id'    => ['required', 'exists:users,id', 'different:kepala_id'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => [Rule::requiredIf($request->input('jenis') === PenugasanPejabat::JENIS_PLH), 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'nomor_surat'     => ['nullable', 'string', 'max:100'],
            'alasan'          => ['nullable', 'string', 'max:500'],
        ], [
            'kepala_id.required'              => 'Pilih jabatan yang digantikan.',
            'pengganti_id.required'           => 'Pilih pegawai pengganti.',
            'pengganti_id.different'          => 'Pengganti tidak boleh sama dengan kepala yang digantikan.',
            'tanggal_mulai.required'          => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'        => 'Plh wajib punya tanggal selesai.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $kepala  = User::bukanSuperadmin()->findOrFail($data['kepala_id']);
        $jabatan = $kepala->jabatanKepala();

        if (! $jabatan) {
            return back()->withInput()->withErrors(['kepala_id' => 'Akun ini bukan kepala yang dapat digantikan.']);
        }

        $pengganti = User::bukanSuperadmin()->findOrFail($data['pengganti_id']);
        $selesai   = $data['tanggal_selesai'] ?? null;

        if ($this->service->penugasanBentrok($jabatan, $data['tanggal_mulai'], $selesai)) {
            return back()->withInput()->withErrors([
                'kepala_id' => 'Jabatan ini sudah punya penugasan aktif pada periode tersebut. Gunakan "Tukar Pengganti" atau cabut penugasan lama.',
            ]);
        }

        [$role, $bagianId, $subId] = $jabatan;

        $penugasan = PenugasanPejabat::create([
            'jenis'                => $data['jenis'],
            'jabatan_role'         => $role,
            'bagian_bidang_id'     => $bagianId,
            'sub_bagian_seksi_id'  => $subId,
            // Plh: kepala yang berhalangan sementara. Plt: jabatan lowong, tidak ada pejabat definitif.
            'pejabat_definitif_id' => $data['jenis'] === PenugasanPejabat::JENIS_PLH ? $kepala->id : null,
            'pengganti_id'         => $pengganti->id,
            'tanggal_mulai'        => $data['tanggal_mulai'],
            'tanggal_selesai'      => $selesai,
            'status'               => PenugasanPejabat::STATUS_AKTIF,
            'nomor_surat'          => $data['nomor_surat'] ?? null,
            'alasan'               => $data['alasan'] ?? null,
            'dibuat_oleh'          => $request->user()->id,
        ]);

        Log::info('Superadmin menunjuk ' . $data['jenis'], [
            'superadmin_id' => $request->user()->id,
            'penugasan_id'  => $penugasan->id,
            'pengganti_id'  => $pengganti->id,
        ]);

        $redirect = redirect()->route('superadmin.penugasan.index')
            ->with('success', "{$pengganti->name} berhasil ditunjuk sebagai {$penugasan->label_jenis}.");

        return $this->tambahPeringatanCuti($redirect, $pengganti, $data['tanggal_mulai'], $selesai);
    }

    public function tukarForm($id)
    {
        $lama = PenugasanPejabat::statusAktif()
            ->with(['pengganti', 'pejabatDefinitif', 'bagianBidang', 'subBagianSeksi'])
            ->findOrFail($id);

        $kepala = $this->service->kepalaDariJabatan($lama);
        $rek    = $kepala ? $this->service->rekomendasiPengganti($kepala)->pluck('id')->all() : [];

        $opsi = User::bukanSuperadmin()
            ->where('id', '!=', $lama->pengganti_id)
            ->when($kepala, fn ($q) => $q->where('id', '!=', $kepala->id))
            ->orderBy('name')
            ->get(['id', 'name', 'nip'])
            ->map(fn ($u) => ['id' => $u->id, 'nama' => $u->name, 'rek' => in_array($u->id, $rek)])
            ->sortBy([['rek', 'desc'], ['nama', 'asc']])
            ->values();

        $cutiPengganti = $this->service->cutiBentrok($lama->pengganti_id, now())->first();

        return view('superadmin.penugasan.tukar', compact('lama', 'opsi', 'cutiPengganti'));
    }

    public function tukar(Request $request, $id)
    {
        $lama = PenugasanPejabat::statusAktif()->findOrFail($id);

        if ($lama->status_efektif === 'selesai') {
            return redirect()->route('superadmin.penugasan.index')
                ->with('error', 'Penugasan ini sudah berakhir, tidak bisa ditukar.');
        }

        $data = $request->validate([
            'pengganti_id' => ['required', 'exists:users,id'],
            'alasan'       => ['required', 'string', 'max:500'],
            'nomor_surat'  => ['nullable', 'string', 'max:100'],
        ], [
            'pengganti_id.required' => 'Pilih pengganti yang baru.',
            'alasan.required'       => 'Alasan penukaran wajib diisi (tercatat di riwayat).',
        ]);

        $baruId = (int) $data['pengganti_id'];

        if ($baruId === (int) $lama->pengganti_id || $baruId === (int) $lama->pejabat_definitif_id) {
            return back()->withInput()->withErrors([
                'pengganti_id' => 'Pilih pegawai yang berbeda dari pengganti saat ini dan dari kepala yang digantikan.',
            ]);
        }

        $pengganti = User::bukanSuperadmin()->findOrFail($baruId);

        $baru = DB::transaction(function () use ($lama, $pengganti, $data, $request) {
            $lama->update([
                'status'           => PenugasanPejabat::STATUS_DIALIHKAN,
                'keterangan_akhir' => $data['alasan'],
                'berakhir_pada'    => now(),
            ]);

            $hariIni = now()->startOfDay();

            return PenugasanPejabat::create([
                'jenis'                => $lama->jenis,
                'jabatan_role'         => $lama->jabatan_role,
                'bagian_bidang_id'     => $lama->bagian_bidang_id,
                'sub_bagian_seksi_id'  => $lama->sub_bagian_seksi_id,
                'pejabat_definitif_id' => $lama->pejabat_definitif_id,
                'pengganti_id'         => $pengganti->id,
                'tanggal_mulai'        => $lama->tanggal_mulai->gt($hariIni) ? $lama->tanggal_mulai : $hariIni,
                'tanggal_selesai'      => $lama->tanggal_selesai,
                'status'               => PenugasanPejabat::STATUS_AKTIF,
                'nomor_surat'          => $data['nomor_surat'] ?? null,
                'alasan'               => $lama->alasan,
                'dialihkan_dari_id'    => $lama->id,
                'dibuat_oleh'          => $request->user()->id,
            ]);
        });

        Log::info('Superadmin menukar pengganti ' . $lama->jenis, [
            'superadmin_id' => $request->user()->id,
            'dari'          => $lama->id,
            'ke'            => $baru->id,
        ]);

        $redirect = redirect()->route('superadmin.penugasan.index')
            ->with('success', "Pengganti diganti menjadi {$pengganti->name}. Riwayat penukaran tercatat.");

        return $this->tambahPeringatanCuti(
            $redirect,
            $pengganti,
            $baru->tanggal_mulai->toDateString(),
            $baru->tanggal_selesai?->toDateString()
        );
    }

    public function cabut(Request $request, $id)
    {
        $penugasan = PenugasanPejabat::statusAktif()->findOrFail($id);

        $data = $request->validate([
            'keterangan' => ['required', 'string', 'max:500'],
        ], ['keterangan.required' => 'Alasan pencabutan wajib diisi.']);

        $penugasan->update([
            'status'           => PenugasanPejabat::STATUS_DICABUT,
            'keterangan_akhir' => $data['keterangan'],
            'berakhir_pada'    => now(),
        ]);

        Log::info('Superadmin mencabut penugasan', [
            'superadmin_id' => $request->user()->id,
            'penugasan_id'  => $penugasan->id,
        ]);

        return redirect()->route('superadmin.penugasan.index')->with('success', 'Penugasan berhasil dicabut.');
    }

    /** Tambahkan flash "warning" kalau pengganti punya cuti disetujui di periode tsb (tidak memblokir). */
    private function tambahPeringatanCuti($redirect, User $pengganti, $mulai, $selesai)
    {
        $bentrok = $this->service->cutiBentrok($pengganti->id, $mulai, $selesai);

        if ($bentrok->isEmpty()) {
            return $redirect;
        }

        $c = $bentrok->first();

        return $redirect->with('warning', sprintf(
            'Perhatian: %s memiliki cuti disetujui pada %s – %s. Pertimbangkan menukar pengganti.',
            $pengganti->name,
            Carbon::parse($c->tanggal_mulai)->format('d M Y'),
            Carbon::parse($c->tanggal_selesai)->format('d M Y')
        ));
    }
}