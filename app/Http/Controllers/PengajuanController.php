<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\JenisCuti;
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusCutiNotification;

class PengajuanController extends Controller
{
    protected array $alurKepala = [
        'Kepala Seksi'      => ['step' => 1, 'lanjut' => 2],
        'Kepala Bidang'     => ['step' => 2, 'lanjut' => 3],
        'Kepala Sub-Bagian' => ['step' => 4, 'lanjut' => 5],
        'Kepala TU'         => ['step' => 5, 'lanjut' => 6],
        'Kepala Kantor'     => ['step' => 6, 'lanjut' => 7],
    ];
        /**
     * Peran meja yang sedang dipegang $user atas $pengajuan (null = bukan mejanya).
     */
    protected function mejaSaya(PengajuanCuti $pengajuan, $user): ?string
    {
        return $this->mejaSayaDetail($pengajuan, $user)['peran'] ?? null;
    }

    /**
     * Versi lengkap: selain peran, juga apakah hak datang dari meja sendiri atau dari PLH.
     *
     * @return array{peran:string, via_plh:bool, kepala:?\App\Models\User}|null
     */
    protected function mejaSayaDetail(PengajuanCuti $pengajuan, $user): ?array
    {
        $step = (int) $pengajuan->approval_step;

        // 1) Meja milik sendiri
        foreach ($this->alurKepala as $peran => $info) {
            if (! $user->hasRole($peran) || $step !== $info['step']) {
                continue;
            }

            if (! $this->unitCocok($pengajuan, $peran, $user)) {
                continue;
            }

            return ['peran' => $peran, 'via_plh' => false, 'kepala' => null];
        }

        // 2) Meja Kepala yang sedang cuti dan menunjuk $user sebagai PLH-nya.
        foreach ($user->penugasanPlhAktif()->get() as $tugas) {
            $kepala = $tugas->user;

            if (! $kepala) {
                continue;
            }

            $peran = $this->peranMejaKepala($kepala);

            if ($peran === null
                || $step !== $this->alurKepala[$peran]['step']
                || ! $this->unitCocok($pengajuan, $peran, $kepala)) {
                continue;
            }

            return ['peran' => $peran, 'via_plh' => true, 'kepala' => $kepala];
        }

                // 3) Meja jabatan yang digantikan lewat penugasan Superadmin (Plh / Plt).
        foreach ($user->penugasanPejabatBerlaku()->get() as $tugas) {
            $peran = $tugas->jabatan_role;

            if (! isset($this->alurKepala[$peran])
                || $peran === 'Kepala Kantor'
                || $step !== $this->alurKepala[$peran]['step']
                || ! $this->unitCocok($pengajuan, $peran, $tugas)) {
                continue;
            }

            return [
                'peran'   => $peran,
                'via_plh' => true,
                'kepala'  => $tugas->pejabatDefinitif, // null untuk Plt (jabatan lowong)
                'jenis'   => $tugas->jenis,            // PLH | PLT
            ];
        }

        return null;
    }

    /** Pemohon ada di unit yang dikelola pemegang meja? (Kasi = seksi, Kabid = bidang) */
    protected function unitCocok(PengajuanCuti $pengajuan, string $peran, $pemegangMeja): bool
    {
        return match ($peran) {
            'Kepala Seksi'  => $pengajuan->user?->sub_bagian_seksi_id === $pemegangMeja->sub_bagian_seksi_id,
            'Kepala Bidang' => $pengajuan->user?->bagian_bidang_id === $pemegangMeja->bagian_bidang_id,
            default         => true,
        };
    }

    /** Peran meja milik seorang Kepala. Kepala Kantor dikecualikan (jalur PLT). */
    protected function peranMejaKepala($kepala): ?string
    {
        foreach (array_keys($this->alurKepala) as $peran) {
            if ($peran !== 'Kepala Kantor' && $kepala->hasRole($peran)) {
                return $peran;
            }
        }

        return null;
    }

    /** Peran meja milik user sendiri (urutan prioritas sama seperti kode lama). */
    protected function peranMejaSendiri($user): ?string
    {
        foreach (array_keys($this->alurKepala) as $peran) {
            if ($user->hasRole($peran)) {
                return $peran;
            }
        }

        return null;
    }

    /** Filter query ke "meja ini": step milik peran + unit pemegang meja. */
    protected function terapkanMeja($query, string $peran, $pemegangMeja): void
    {
        $query->where('approval_step', $this->alurKepala[$peran]['step']);

        if ($peran === 'Kepala Seksi') {
            $query->whereHas('user', fn ($u) => $u->where('sub_bagian_seksi_id', $pemegangMeja->sub_bagian_seksi_id));
        } elseif ($peran === 'Kepala Bidang') {
            $query->whereHas('user', fn ($u) => $u->where('bagian_bidang_id', $pemegangMeja->bagian_bidang_id));
        }
    }

        public function index()
    {
        $riwayat = PengajuanCuti::where('user_id', Auth::id())->latest()->paginate(5);
        $user = Auth::user();

        $jenisCutis = JenisCuti::query()
            ->when($user->status_kepegawaian === 'PPPK', function ($q) {
                $q->where('untuk_pppk', true);
            })
            ->when($user->jenis_kelamin === 'L', function ($q) {
                $q->where('khusus_perempuan', false);
            })
            ->get();

        return view('pegawai.pengajuan', compact('riwayat', 'jenisCutis'));
    }
private function hitungHariKerja(\Carbon\Carbon $mulai, \Carbon\Carbon $selesai): int
{
    $libur = \App\Models\HariLibur::whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
        ->get()->map(fn ($h) => $h->tanggal->toDateString())->all();

    $n = 0;
    foreach (\Carbon\CarbonPeriod::create($mulai, $selesai) as $hari) {
        if ($hari->isWeekend() || in_array($hari->toDateString(), $libur, true)) {
            continue;
        }
        $n++;
    }
    return $n;
}
    public function store(Request $request)
{
    $request->validate([
        'jenis_cuti_id'     => 'required|exists:jenis_cutis,id',
        'tanggal_mulai'     => 'required|date',
        'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
        'alasan'            => 'required|string',
        'lokasi'            => 'required|string|max:255',
        'surat_pengajuan'   => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        'bukti_pendukung'   => 'nullable|array|max:5',
        'bukti_pendukung.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
    ]);
     $jenisCutiDipilih = \App\Models\JenisCuti::find($request->jenis_cuti_id);

    if (Auth::user()->status_kepegawaian === 'PPPK' && $jenisCutiDipilih && !$jenisCutiDipilih->untuk_pppk) {
        return redirect()->back()->withInput()->with('error', 'Jenis cuti "' . $jenisCutiDipilih->nama_cuti . '" tidak tersedia untuk status kepegawaian PPPK.');
    }
    if (Auth::user()->jenis_kelamin === 'L' && $jenisCutiDipilih && $jenisCutiDipilih->khusus_perempuan) {
        return redirect()->back()->withInput()->with('error', 'Jenis cuti "' . $jenisCutiDipilih->nama_cuti . '" khusus untuk pegawai perempuan.');
    }
    if ($jenisCutiDipilih && $jenisCutiDipilih->wajib_lampiran && !$request->hasFile('bukti_pendukung')) {
    return redirect()->back()->withInput()->with('error', 'Jenis cuti "' . $jenisCutiDipilih->nama_cuti . '" wajib melampirkan bukti pendukung (surat dokter / surat keterangan / surat rawat inap).');
    }

    $tanggal_mulai = \Carbon\Carbon::parse($request->tanggal_mulai);
    $tanggal_selesai = \Carbon\Carbon::parse($request->tanggal_selesai);
    

    $durasi_hari = $tanggal_mulai->diffInWeekdays($tanggal_selesai->copy()->addDay());

    // Validasi pencegahan jika user murni mengajukan hanya di hari libur (misal: Sabtu ke Minggu)
    if ($durasi_hari < 1) {
        return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid. Pengajuan cuti tidak bisa dilakukan di hari libur akhir pekan.');
    }
    $batasBulan = ['Cuti Alasan Penting' => 1, 'Cuti Melahirkan' => 3, 'Cuti Besar' => 3];
    $namaJenisDipilih = $jenisCutiDipilih->nama_cuti ?? '';
        if (isset($batasBulan[$namaJenisDipilih])
        && $tanggal_selesai->gt($tanggal_mulai->copy()->addMonths($batasBulan[$namaJenisDipilih]))) {
        return redirect()->back()->withInput()->with('error', $namaJenisDipilih . ' paling lama ' . $batasBulan[$namaJenisDipilih] . ' bulan.');
    }

    $user = Auth::user();

    $adaBentrok = PengajuanCuti::where('user_id', $user->id)
        ->whereNotIn('approval_step', [0, 10])
        ->where('tanggal_mulai', '<=', $tanggal_selesai)
        ->where('tanggal_selesai', '>=', $tanggal_mulai)
        ->exists();

    if ($adaBentrok) {
        return redirect()->back()->withInput()->with('error', 'Anda masih memiliki pengajuan cuti aktif yang tanggalnya bertumpuk dengan rentang tanggal ini. Selesaikan atau batalkan pengajuan sebelumnya terlebih dahulu.');
    }
    if ($jenisCutiDipilih && $jenisCutiDipilih->mengurangi_kuota) {
    $tahunCuti = $tanggal_mulai->year;

    $tercatat = (int) PengajuanCuti::where('user_id', $user->id)
        ->where('approval_step', 8)
        ->whereYear('tanggal_mulai', $tahunCuti)
        ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
        ->sum('durasi_hari');

    $menunggu = (int) PengajuanCuti::where('user_id', $user->id)
        ->whereNotIn('approval_step', [0, 8, 10])
        ->whereYear('tanggal_mulai', $tahunCuti)
        ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
        ->sum('durasi_hari');

    $saldoCek  = \App\Models\User::saldoDari($user, $tercatat, $user->punyaCutiBesar($tahunCuti));
    $sisaBebas = $saldoCek['total_sisa'] - $menunggu;

    if ($tahunCuti === now()->year && $durasi_hari > $sisaBebas) {
        return redirect()->back()->withInput()->with('error',
            "Sisa cuti Anda {$sisaBebas} hari (sudah dikurangi pengajuan yang masih diproses), sedangkan pengajuan ini {$durasi_hari} hari.");
    }
    }

    // 1. Upload Berkas Surat Pengajuan
    $suratFile = $request->file('surat_pengajuan');
    $suratName = time() . '_wajib_' . preg_replace('/\s+/', '_', $suratFile->getClientOriginalName());
    $suratPath = $suratFile->storeAs('dokumen/surat_pengajuan', $suratName, 'public');
 
    // 2. Upload Berkas Bukti Pendukung (Opsional / Multiple)
    $buktiPaths = [];
    if ($request->hasFile('bukti_pendukung')) {
        foreach ($request->file('bukti_pendukung') as $key => $file) {
            $buktiName = time() . '_opsi' . $key . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $buktiPaths[] = $file->storeAs('dokumen/bukti_pendukung', $buktiName, 'public');
        }
    }

    // Memeriksa apakah pegawai ini berada di ekosistem Tata Usaha (TU)
    $is_tu = $user->bagianBidang ? $user->bagianBidang->is_tu : false;

    // PENTING: cek role Kepala milik SI PENGAJU dulu, sebelum cek $is_tu.
    // Sebelumnya $is_tu dicek duluan, jadi Kepala Sub-Bagian & Kepala TU
    // (yang bagian_bidang-nya memang TU) selalu ke-set ke step 3 tanpa
    // pernah sampai ke logic "potong kompas" di bawah — itu sebabnya
    // pengajuan mereka bisa nyangkut balik ke approval_step milik mereka
    // sendiri (self-approval).
    if ($user->hasRole('Kepala Seksi') || $user->hasRole('Admin Kepegawaian')) {
        $inisialStep = 2; // Lompat ke Kepala Bidang
    } elseif ($user->hasRole('Kepala Bidang')) {
        $inisialStep = 3; // Lompat ke Verifikasi Admin
    } elseif ($user->hasRole('Kepala Sub-Bagian')) {
        $inisialStep = 5; // Lompat ke Kepala TU, skip approval diri sendiri (step 4)
    } elseif ($user->hasRole('Kepala TU')) {
        $inisialStep = 6; // Lompat ke Kepala Kantor, skip approval diri sendiri (step 5)
    } elseif ($user->hasRole('Kepala Kantor')) {
        $inisialStep = 7; // Lompat ke Finalisasi Admin, skip approval diri sendiri (step 6)
    } elseif ($is_tu) {
        // JALUR TATA USAHA: Pegawai (non-kepala) TU langsung masuk ke Dashboard Admin
        $inisialStep = 3;
    } else {
        // JALUR OPERASIONAL BIDANG: Pegawai biasa masuk ke Kasi (Step 1)
        $inisialStep = 1;
    }

    // Menentukan Teks Status Berdasarkan Hierarki
    $statusPengajuan = 'Menunggu Persetujuan';
    if ($user->hasRole('Kepala Kantor')) {
        $statusPengajuan = 'Disetujui Otomatis (Pimpinan)';
    } else {
        // Mapping teks status berdasarkan step awal agar informatif
        $jabatanMap = [
            1 => 'Menunggu Kepala Seksi',
            2 => 'Menunggu Kepala Bidang',
            3 => 'Menunggu Verifikasi Admin / TU',
            4 => 'Menunggu Kepala Sub Bagian',
            5 => 'Menunggu Kepala TU',
            6 => 'Menunggu Kepala Kantor'
        ];
        $statusPengajuan = $jabatanMap[$inisialStep] ?? 'Menunggu Persetujuan';
    }

    // 3. Simpan ke Database
    $jenisCuti = \App\Models\JenisCuti::find($request->jenis_cuti_id);
$namaCuti = $jenisCuti ? $jenisCuti->nama_cuti : '';
    $prefix = 'CT';
if (str_contains($namaCuti, 'Sakit')) {
    $prefix = 'CS';
} elseif (str_contains($namaCuti, 'Pengganti')) {
    $prefix = 'CPB';
} elseif (str_contains($namaCuti, 'Tahunan')) {
    $prefix = 'CT';
} elseif (str_contains($namaCuti, 'Alasan Penting')) {
    $prefix = 'CAP';
} elseif (str_contains($namaCuti, 'Besar')) {
    $prefix = 'CB';
}

$lastPengajuan = \App\Models\PengajuanCuti::where('jenis_cuti_id', $request->jenis_cuti_id)
    ->orderBy('id', 'desc')
    ->first();

$nomorUrut = 1;
if ($lastPengajuan && $lastPengajuan->kode_pengajuan) {
    // PENTING: pakai strlen($prefix), BUKAN angka 2 yang di-hardcode.
    // Prefix 'CT'/'CS'/'CB' memang 2 huruf, tapi 'CAP' (Cuti Alasan
    // Penting) itu 3 huruf — substr(..., 2) pada "CAP01" menghasilkan
    // "P01", lalu (int)"P01" jadi 0, sehingga nomorUrut SELALU 1 buat
    // jenis cuti ini dan pengajuan CAP kedua ke atas pasti tabrakan
    // kode dengan yang pertama.
    $lastUrut = (int) substr($lastPengajuan->kode_pengajuan, strlen($prefix));
    $nomorUrut = $lastUrut + 1;
}

$kodeBaru = $prefix . str_pad($nomorUrut, 2, '0', STR_PAD_LEFT);
    PengajuanCuti::create([
        'kode_pengajuan'    => $kodeBaru,
        'user_id'           => $user->id,
        'jenis_cuti_id'     => $request->jenis_cuti_id,
        'tanggal_mulai'     => $request->tanggal_mulai,
        'tanggal_selesai'   => $request->tanggal_selesai,
        'durasi_hari'       => $durasi_hari, // <--- TAMBAHAN UNTUK MENYIMPAN DURASI 
        'alasan'            => $request->alasan,
        'lokasi'            => $request->lokasi,
        'surat_pengajuan'   => $suratPath,
        'bukti_pendukung'   => empty($buktiPaths) ? null : $buktiPaths,
        'approval_step'     => $inisialStep, 
    ]);


    return redirect()->route('pengajuan.index')->with('success', 'Pengajuan cuti dan dokumen lampiran berhasil dikirim.');
}

    public function riwayat(Request $request)
    {

       $query = PengajuanCuti::where('user_id', Auth::id())->with('jenisCuti')->orderByDesc('tanggal_mulai');

        // 1. Filter Pencarian Kata Kunci (Alasan) - Asli buatanmu
        if ($request->has('cari') && $request->cari != '') {
            $query->where('alasan', 'like', '%' . $request->cari . '%');
        }

        // 2. Filter Jenis Cuti
        if ($request->has('jenis_cuti') && $request->jenis_cuti != '') {
            $query->where('jenis_cuti_id', $request->jenis_cuti);
        }

        // 3. Filter Bulan (Dari tanggal mulai)
        if ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        // 4. Filter Tahun (Dari tanggal mulai)
        if ($request->has('tahun') && $request->tahun != '') {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        // Gunakan variabel aslimu ($riwayat) dan limit 5
        $riwayat = $query->paginate(10)->withQueryString();
        
        // Ambil data jenis cuti untuk mengisi pilihan di dropdown HTML nanti
        $jenis_cutis = \App\Models\JenisCuti::all();

        return view('pegawai.riwayat', compact('riwayat', 'jenis_cutis')); 
    }
    public function batal($id)
    {
        $pengajuan = \App\Models\PengajuanCuti::findOrFail($id);

    if (auth()->id() !== $pengajuan->user_id) {
        abort(403, 'Anda hanya dapat membatalkan pengajuan cuti Anda sendiri.');
    }

    $stepFinal = [0, 8, 10]; // Ditolak, Disetujui, Dibatalkan — sesuaikan kalau "Perlu Revisi" mau tetap boleh dibatalkan

    if (in_array($pengajuan->approval_step, $stepFinal)) {
        return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses final atau sudah ditutup.');
    }

    $pengajuan->update(['approval_step' => 10]);

    return redirect()->back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }   

       public function show($id)
    {
        $pengajuan = PengajuanCuti::with('plh')->where('user_id', Auth::id())->findOrFail($id);

        // Dropdown PLH hanya dimuat saat memang bisa dipilih (Kepala, step 7)
        $kandidatPlh = collect();
        if ($pengajuan->butuhPlh() && (int) $pengajuan->approval_step === 7) {
            $kandidatPlh = $pengajuan->kandidatPlh()->with('subBagianSeksi')->orderBy('name')->get();
        }

        return view('pegawai.detail', compact('pengajuan', 'kandidatPlh'));
    }

    public function simpanPlh(Request $request, $id)
    {
        $pengajuan = PengajuanCuti::with('user')->findOrFail($id);
        $user      = Auth::user();

        if ((int) $pengajuan->user_id !== (int) $user->id) {
            abort(403, 'Anda hanya dapat menunjuk PLH untuk pengajuan cuti Anda sendiri.');
        }

        if (! $pengajuan->butuhPlh()) {
            return back()->with('error', 'Pengajuan ini tidak memerlukan penunjukan PLH.');
        }

        if ((int) $pengajuan->approval_step !== 7) {
            return back()->with('error', 'PLH hanya dapat ditunjuk setelah pengajuan melewati seluruh persetujuan dan menunggu finalisasi Admin.');
        }

        $request->validate(
            ['plh_user_id' => 'required|integer'],
            ['plh_user_id.required' => 'Silakan pilih pegawai yang akan menjadi PLH.']
        );

        // Validasi ulang di server: pilihan HARUS ada di daftar kandidat valid.
        $kandidat = $pengajuan->kandidatPlh()->whereKey($request->plh_user_id)->first();

        if (! $kandidat) {
            return back()->with('error', 'Pegawai yang dipilih tidak termasuk kandidat PLH yang valid untuk pengajuan ini.');
        }

        $pengajuan->plh_user_id = $kandidat->id;
        $pengajuan->save();

        return redirect()->route('pegawai.detail', $pengajuan->id)
            ->with('success', "{$kandidat->name} berhasil ditunjuk sebagai PLH. Admin kini dapat memfinalisasi pengajuan Anda.");
    }

    public function notifikasi()
    {
        $notifikasis = Auth::user()->notifications;
        $belumDibaca = Auth::user()->unreadNotifications->count();

        return view('pegawai.notifikasi', compact('notifikasis', 'belumDibaca'));
    }
    
    public function tandaiSemuaDibaca()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    } 

    public function indexKepala(Request $request)
    {
        $user = Auth::user();

        // Filter pencarian & tanggal dipakai bareng oleh antrean sendiri dan antrean PLH
        $terapkanFilter = function ($q) use ($request) {
            $q->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%"));
            })
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date));
        };

        // ---- Antrean milik sendiri ----
        // Pegawai biasa yang cuma jadi PLH tidak punya meja sendiri -> antrean kosong.
        $peranSendiri = $this->peranMejaSendiri($user);

        $pengajuans = PengajuanCuti::with(['user', 'jenisCuti'])
            ->where('user_id', '!=', $user->id)
            ->where(function ($q) use ($peranSendiri, $user) {
                if ($peranSendiri) {
                    $this->terapkanMeja($q, $peranSendiri, $user);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->tap($terapkanFilter)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ---- Section terpisah: "Sedang Menjadi PLH Untuk..." ----
        $antreanPlh = $user->penugasanPlhAktif()->get()
            ->unique('user_id')
            ->map(function ($tugas) use ($user, $terapkanFilter) {
                $kepala = $tugas->user;
                $peran  = $kepala ? $this->peranMejaKepala($kepala) : null;

                if (! $peran) {
                    return null;
                }

                $daftar = PengajuanCuti::with(['user', 'jenisCuti'])
                    ->where('user_id', '!=', $user->id)
                    ->where(fn ($q) => $this->terapkanMeja($q, $peran, $kepala))
                    ->tap($terapkanFilter)
                    ->latest()
                    ->get();

                return [
                    'kepala'     => $kepala,
                    'peran'      => $peran,
                    'sampai'     => $tugas->tanggal_selesai,
                    'pengajuans' => $daftar,
                ];
            })
            ->filter()
            ->values();

                // ---- Penugasan Plh/Plt dari Superadmin (tabel penugasan_pejabat) ----
        $antreanPenugasan = $user->penugasanPejabatBerlaku()->get()
            ->map(function ($tugas) use ($user, $terapkanFilter) {
                $peran = $tugas->jabatan_role;

                if (! isset($this->alurKepala[$peran]) || $peran === 'Kepala Kantor') {
                    return null;
                }

                $daftar = PengajuanCuti::with(['user', 'jenisCuti'])
                    ->where('user_id', '!=', $user->id)
                    ->where(fn ($q) => $this->terapkanMeja($q, $peran, $tugas))
                    ->tap($terapkanFilter)
                    ->latest()
                    ->get();

                return [
                    'kepala'     => $tugas->pejabatDefinitif,
                    'label'      => $tugas->pejabatDefinitif?->name ?? $tugas->nama_jabatan,
                    'jenis'      => $tugas->jenis,
                    'peran'      => $peran,
                    'sampai'     => $tugas->tanggal_selesai,
                    'pengajuans' => $daftar,
                ];
            })
            ->filter();

        $antreanPlh = $antreanPlh->concat($antreanPenugasan)->values();

        return view('kepala.approval.index', compact('pengajuans', 'antreanPlh', 'peranSendiri'));
    }

        public function showKepala($id)
    {
        $data = \App\Models\PengajuanCuti::with('user')->find($id);

        if (!$data) {
            $data = new \App\Models\PengajuanCuti();
            $data->id = $id;
            $data->nomor_pengajuan = 'CT.2026.DUMMY-' . $id;
            $data->jenis_cuti = 'Cuti Tahunan (Mode Dummy)';
            $data->durasi_hari = 3;
            $data->tanggal_mulai = now()->addDays(7);
            $data->created_at = now();
            $data->alasan = 'Ini adalah teks dummy sementara. Sistem tidak menemukan ID ' . $id . ' di database.';
            $data->lampiran = null;
            $data->approval_step = 2;
            $data->status = 'Menunggu';

            $dummyUser = new \App\Models\User();
            $dummyUser->name = 'Budi Dummy (Tester)';
            $data->setRelation('user', $dummyUser);

            return view('kepala.approval.show', compact('data'));
        }

        $user = Auth::user();

        if ($data->user_id === $user->id) {
            abort(403, 'Anda tidak dapat membuka pengajuan cuti Anda sendiri melalui halaman approval.');
        }

        $meja = $this->mejaSayaDetail($data, $user);

        if ($meja === null) {
            abort(403, 'Pengajuan ini bukan/belum berada di meja Anda.');
        }

        return view('kepala.approval.show', compact('data', 'meja'));
    }

    // =================================================================
    // FUNGSI UNTUK ADMIN KEPEGAWAIAN
    // =================================================================
    
 // Gunakan nama ini (tanpa 's') agar cocok dengan routes/web.php milikmu
    public function indexApproval(Request $request)
    {
        $user = Auth::user();
        
        // 1. Kerangka Dasar Query 
        $query = \App\Models\PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi', 'jenisCuti']);

        // 2. FILTER HIERARKI JABATAN
        if ($user->hasRole('Admin Kepegawaian')) {
            // Biarkan kosong. Admin di halaman ini berhak melihat semua riwayat.
        } elseif ($user->level_jabatan == 'Kepala Seksi/Sub-Bagian') {
            if ($user->bagianBidang && $user->bagianBidang->is_tu) {
                $query->where('approval_step', 4);
            } else {
                $query->where('approval_step', 1)->whereHas('user', function($q) use ($user) {
                    $q->where('sub_bagian_seksi_id', $user->sub_bagian_seksi_id);
                });
            }
        } elseif ($user->level_jabatan == 'Kepala Bagian/Bidang') {
            if ($user->bagianBidang && $user->bagianBidang->is_tu) {
                $query->where('approval_step', 5);
            } else {
                $query->where('approval_step', 2)->whereHas('user', function($q) use ($user) {
                    $q->where('bagian_bidang_id', $user->bagian_bidang_id);
                });
            }
        } elseif ($user->level_jabatan == 'Kepala Kantor') {
            $query->where('approval_step', 6);
        }

        // 3. FILTER PENCARIAN KOTAK TEKS
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // 4. Filter Status Dropdown
               if ($request->filled('status') && $request->status !== 'Semua Status') {
            match ($request->status) {
                'Menunggu'   => $query->whereNotIn('approval_step', [0, 8, 10]), // termasuk step 9, sama seperti badge
                'Disetujui'  => $query->where('approval_step', 8),
                'Ditolak'    => $query->where('approval_step', 0),
                'Dibatalkan' => $query->where('approval_step', 10),
                default      => null,
            };
               }
               
       if ($request->filled('status') && $request->status !== 'Semua Status') {
    if ($request->status == 'Menunggu') {
        $query->whereNotIn('approval_step', [0, 8, 9, 10]);
    } elseif ($request->status == 'Disetujui') {
        $query->where('approval_step', 8);
    } elseif ($request->status == 'Ditolak') {
        $query->where('approval_step', 0);
    } elseif ($request->status == 'Dibatalkan') {
        $query->where('approval_step', 10);
    }
}
        if ($request->filled('status') && $request->status !== 'Semua Status') {
            if ($request->status == 'Menunggu') {
                $query->whereNotIn('approval_step', [0, 8, 9, 10]);
            } elseif ($request->status == 'Disetujui') {
                $query->where('approval_step', 8);
            } elseif ($request->status == 'Ditolak') {
                $query->where('approval_step', 0);
            } elseif ($request->status == 'Dibatalkan') {
                $query->where('approval_step', 10);
            }
        }

        // 5. FILTER TANGGAL
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 6. EKSEKUSI AKHIR (Sangat Penting: Harus paginate, BUKAN get)
        $pengajuans = $query->latest()->paginate(10)->onEachSide(1)->withQueryString();

        return view('admin.approval.index', compact('pengajuans'));
    }


    public function showApproval($id)
    {
        // cari data asli di database terlebih dahulu
        $data = \App\Models\PengajuanCuti::with(['user', 'jenisCuti'])->find($id);
        
        return view('admin.approval.show', compact('data'));
    }

    public function verifikasiAdmin(Request $request, $id)
    {
        $action = $request->input('action');
        $catatan = $request->input('catatan'); 

        $pengajuan = \App\Models\PengajuanCuti::find($id);

        // =========================================================
        // JIKA DATA TIDAK ADA (MODE DUMMY)
        // =========================================================
        if (!$pengajuan) {
            if (in_array($id, [1, 2, 3])) { 
                if ($action == 'setujui') {
                    return redirect()->route('admin.approval.index')->with('success', '(Mode Dummy) Berkas berhasil diteruskan/diselesaikan.');
                } elseif ($action == 'revisi') {
                    return redirect()->route('admin.approval.index')->with('warning', '(Mode Dummy) Berkas direvisi dengan catatan: ' . $catatan);
                } elseif ($action == 'tolak') {
                    return redirect()->route('admin.approval.index')->with('error', '(Mode Dummy) Berkas ditolak dengan alasan: ' . $catatan);
                }
            }
            abort(404);
        }

        // =========================================================
        // JIKA DATA ASLI (DATABASE)
        // =========================================================
        if ($action == 'setujui') {
            if ($pengajuan->approval_step == 3) {
                // Admin meneruskan ke Kasubag — sertakan dokumen bertanda tangan (opsional)
                $request->validate([
                    'dokumen_ttd' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
                ]);

                if ($request->hasFile('dokumen_ttd')) {
                    $file = $request->file('dokumen_ttd');
                    $namaFile = time() . '_ttd_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $path = $file->storeAs('dokumen/ttd_kepala', $namaFile, 'public');

                    $daftarTtd = $pengajuan->dokumen_ttd ?? [];
                    $daftarTtd[] = [
                        'step'  => 3,
                        'peran' => 'Admin Kepegawaian',
                        'nama'  => Auth::user()->name,
                        'file'  => $path,
                        'waktu' => now()->toDateTimeString(),
                    ];
                    $pengajuan->dokumen_ttd = $daftarTtd;
                }

                $pengajuan->approval_step = 4;
                $pengajuan->save();
              } elseif ($pengajuan->approval_step == 7) {
                // GERBANG PLH: Kepala (selain Kepala Kantor) wajib sudah menunjuk PLH
                if ($pengajuan->butuhPlh() && ! $pengajuan->plh_user_id) {
                    return redirect()->route('admin.approval.show', $pengajuan->id)
                        ->with('error', "Tidak bisa difinalisasi: {$pengajuan->user->name} belum menunjuk PLH (Pelaksana Harian). Minta yang bersangkutan menunjuk PLH dari halaman detail pengajuannya, lalu coba lagi.");
                }

                // Admin finalisasi dan selesai (tidak perlu dokumen ttd di tahap ini)
                $pengajuan->update(['approval_step' => 8,]);
            }
            return redirect()->route('admin.approval.index')->with('success', 'Berkas berhasil diproses.');
            
        } elseif ($action == 'revisi' && $pengajuan->approval_step == 7) {
            $request->validate(['catatan' => 'required|string|max:1000'], ['catatan.required' => 'Alasan revisi wajib diisi.']);
            $pengajuan->update([
                'approval_step' => 9, 
                'catatan_penolakan' =>$catatan,
            ]);
            return redirect()->route('admin.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai. Alasan: ' . $catatan);
            
        } elseif ($action == 'tolak' && $pengajuan->approval_step == 7) {
             $request->validate(['catatan' => 'required|string|max:1000'], ['catatan.required' => 'Alasan penolakan wajib diisi.']);
            $pengajuan->update([
                'approval_step' => 0, 
                'catatan_penolakan' => $catatan,
            ]);
            return redirect()->route('admin.approval.index')->with('error', 'Berkas pengajuan cuti ditolak. Alasan: ' . $catatan);
        }
    }
    
    public function notifikasiAdmin()
    {
        $notifikasis = Auth::user()->notifications;
        $belumDibaca = Auth::user()->unreadNotifications->count();

        // Memanggil file view khusus admin
        return view('admin.notifikasi', compact('notifikasis', 'belumDibaca'));
    }
    
    // 1. MESIN TOMBOL SETUJUI
      public function approveKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::with('user')->find($id);

        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('success', '[DUMMY MODE] Seolah-olah berhasil disetujui dan diteruskan!');
        }

        $user = Auth::user();

        if ($pengajuan->user_id === $user->id) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Anda tidak dapat menyetujui pengajuan cuti Anda sendiri.');
        }

       $meja = $this->mejaSayaDetail($pengajuan, $user);

        if ($meja === null) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Pengajuan ini bukan lagi di meja Anda, atau sudah diproses pihak lain.');
        }

        $peranAktif = $meja['peran'];

        $stepBerikutnya = $this->alurKepala[$peranAktif]['lanjut'];

        // Upload dokumen yang sudah ditandatangani (opsional)
        $request->validate([
            'dokumen_ttd' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if ($request->hasFile('dokumen_ttd')) {
            $file = $request->file('dokumen_ttd');
            $namaFile = time() . '_ttd_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('dokumen/ttd_kepala', $namaFile, 'public');

            $daftarTtd = $pengajuan->dokumen_ttd ?? [];
            $daftarTtd[] = [
                'step'  => $pengajuan->approval_step,
                'peran' => $peranAktif,
                'nama'  => $user->name,
                'file'  => $path,
                'waktu' => now()->toDateTimeString(),
            ];
            $pengajuan->dokumen_ttd = $daftarTtd;
        }

        $pengajuan->approval_step = $stepBerikutnya;
        $pengajuan->save();

        return redirect()->route('kepala.approval.index')->with('success', 'Pengajuan berhasil disetujui dan diteruskan.');
    }
    // 2. MESIN TOMBOL TOLAK
        public function tolakKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::with('user')->find($id);

        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('error', '[DUMMY MODE] Seolah-olah pengajuan ditolak permanen!');
        }

        $user = Auth::user();

        if ($pengajuan->user_id === $user->id) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Anda tidak dapat menolak pengajuan cuti Anda sendiri.');
        }

        if ($this->mejaSaya($pengajuan, $user) === null) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Pengajuan ini bukan lagi di meja Anda, atau sudah diproses pihak lain.');
        }

        $request->validate([
            'catatan' => 'required|string|max:1000',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);


        $pengajuan->approval_step = 0;
        $pengajuan->catatan_penolakan = $request->catatan;

        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('error', 'Pengajuan telah ditolak.');
    }

    // 3. MESIN TOMBOL REVISI
    public function revisiKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::with('user')->find($id);

        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('warning', '[DUMMY MODE] Seolah-olah dikembalikan ke pegawai untuk direvisi!');
        }

        $user = Auth::user();

        if ($pengajuan->user_id === $user->id) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Anda tidak dapat mengembalikan pengajuan cuti Anda sendiri untuk direvisi.');
        }

        if ($this->mejaSaya($pengajuan, $user) === null) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Pengajuan ini bukan lagi di meja Anda, atau sudah diproses pihak lain.');
        }

        $request->validate([
            'catatan' => 'required|string|max:1000',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $pengajuan->approval_step = 9;
        $pengajuan->catatan_penolakan = $request->catatan;

        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai untuk direvisi.');
    }
    public function updateJatahMassal(Request $request)
    {
        $request->validate([
            'jumlah_hari'         => 'required|integer|min:0|max:365',
            'target'              => 'required|in:semua,divisi,sub_bagian',
            'bagian_bidang_id'    => 'required_if:target,divisi|exists:bagian_bidangs,id',
            'sub_bagian_seksi_id' => 'required_if:target,sub_bagian|exists:sub_bagian_seksis,id',
        ]);

        $query = \App\Models\User::query();

        if ($request->target === 'divisi') {
            $query->where('bagian_bidang_id', $request->bagian_bidang_id);
        } elseif ($request->target === 'sub_bagian') {
            $query->where('sub_bagian_seksi_id', $request->sub_bagian_seksi_id);
        }
        // target === 'semua' -> tidak difilter, kena seluruh pegawai

        $jumlahDiubah = $query->update([
            'jatah_cuti' => $request->jumlah_hari, 'saldo_tahun_lalu' => 0, 'koreksi_terpakai' => 0,
        ]);

        return redirect()->route('admin.rekap.index')
            ->with('success', "Jatah cuti berhasil diubah menjadi {$request->jumlah_hari} hari untuk {$jumlahDiubah} pegawai.");
    }
        private function periodeRekapDariRequest(Request $request): array
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) date('Y');
        $bulan = ($request->filled('bulan') && $request->bulan !== 'Semua Bulan')
            ? (int) $request->bulan
            : null;

        return [$tahun, $bulan];
    }

    /**
     * Closure constraint dipakai berulang: "pengajuan yang disetujui, dalam
     * periode tahun/bulan terpilih, dan (kalau ada) jenis cuti tertentu."
     * Dipusatkan di sini supaya kartu statistik, tabel, dan ekspor Excel
     * selalu menghitung dengan definisi yang sama persis.
     */
        private function constraintPengajuanRekap($tahun, $bulan, $jenisCutiId)
    {
        return function ($q) use ($tahun, $bulan, $jenisCutiId) {
            $q->where('approval_step', 8)->whereYear('tanggal_mulai', $tahun);
            if ($bulan) {
                $q->whereMonth('tanggal_mulai', $bulan);
            }
            if ($jenisCutiId) {
                $q->where('jenis_cuti_id', $jenisCutiId);
            }
        };
    }

        public function rekapAdmin(Request $request)
    {
        [$tahun, $bulan] = $this->periodeRekapDariRequest($request);
        $jenisCutiId = ($request->filled('jenis_cuti') && $request->jenis_cuti !== 'Semua Jenis')
            ? (int) $request->jenis_cuti
            : null;

        $constraintPeriode = $this->constraintPengajuanRekap($tahun, $bulan, $jenisCutiId); // ikut filter bulan/jenis
        $constraintTahun   = $this->constraintPengajuanRekap($tahun, null, null);           // setahun penuh

        $query = \App\Models\User::bukanSuperadmin()->with(['bagianBidang', 'subBagianSeksi']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        if ($request->filled('divisi') && $request->divisi !== 'Semua Divisi') {
            $query->where('bagian_bidang_id', $request->divisi);
        }
        if ($request->filled('sub_bagian') && $request->sub_bagian !== 'Semua Sub-Bagian') {
            $query->where('sub_bagian_seksi_id', $request->sub_bagian);
        }
        // Filter jenis/bulan: hanya pegawai yang punya cuti disetujui pada periode itu
        if ($jenisCutiId || $bulan) {
            $query->whereHas('pengajuanCutis', $constraintPeriode);
        }

        // Saldo dihitung setahun penuh (bukan per bulan), supaya sisa cuti selalu benar
        $query->withSum(['pengajuanCutis as metrik_terpakai_tahun' => function ($q) use ($constraintTahun) {
            $constraintTahun($q);
            $q->whereHas('jenisCuti', fn ($jq) => $jq->where('mengurangi_kuota', true));
        }], 'durasi_hari');

        $query->withCount(['pengajuanCutis as metrik_jumlah_ajuan' => function ($q) use ($constraintPeriode) {
            $constraintPeriode($q);
        }]);

        $query->withMax(['pengajuanCutis as metrik_pengajuan_terakhir' => function ($q) use ($constraintPeriode) {
            $constraintPeriode($q);
        }], 'created_at');
        $query->withExists(['pengajuanCutis as punya_cuti_besar' => function ($q) use ($tahun) {
            $q->where('approval_step', 8)
              ->whereYear('tanggal_mulai', $tahun)
              ->whereHas('jenisCuti', fn ($j) => $j->where('nama_cuti', 'Cuti Besar'));
        }]);

        $sortBy  = $request->input('sort_by', 'nama');
        $sortDir = $request->input('sort_dir') === 'desc' ? 'desc' : 'asc';

        switch ($sortBy) {
            case 'tanggal_pengajuan':
                $query->orderByRaw('metrik_pengajuan_terakhir IS NULL, metrik_pengajuan_terakhir ' . $sortDir);
                break;
            case 'jumlah_ajuan':
                $query->orderBy('metrik_jumlah_ajuan', $sortDir);
                break;
            case 'jumlah_terpakai':
                $query->orderByRaw('COALESCE(metrik_terpakai_tahun, 0) ' . $sortDir);
                break;
            case 'sisa_jatah':
                $query->orderByRaw('(CASE WHEN punya_cuti_besar = 1 THEN LEAST(COALESCE(jatah_cuti, 12), saldo_tahun_lalu) ELSE COALESCE(jatah_cuti, 12) END - COALESCE(metrik_terpakai_tahun, 0) - COALESCE(koreksi_terpakai, 0)) ' . $sortDir);
                break;
            case 'nama':
            default:
                $query->orderBy('name', $sortDir);
                break;
        }
        $query->orderBy('id', 'asc');

        $rekaps = $query->paginate(10)->onEachSide(1)->through(function ($user) {
            $saldo = \App\Models\User::saldoDari(
                $user,
                (int) ($user->metrik_terpakai_tahun ?? 0),
                (bool) $user->punya_cuti_besar
            );

            return (object) array_merge($saldo, [
                'id'                 => $user->id,
                'nama'               => $user->name,
                'nip'                => $user->nip,
                'divisi'             => $user->subBagianSeksi->nama ?? $user->bagianBidang->nama ?? '-',
                'jumlah_ajuan'       => (int) ($user->metrik_jumlah_ajuan ?? 0),
                'sisa'               => $saldo['total_sisa'],
                'pengajuan_terakhir' => $user->metrik_pengajuan_terakhir,
            ]);
        });

        $rekaps->appends(request()->query());

        $daftarDivisi    = \App\Models\BagianBidang::withCount('users')->orderBy('nama')->get();
        $daftarSubBagian = \App\Models\SubBagianSeksi::with('bagianBidang')->withCount('users')->orderBy('nama')->get();
        $daftarJenisCuti = \App\Models\JenisCuti::orderBy('nama_cuti')->get();

        $daftarTahun = \App\Models\PengajuanCuti::selectRaw('DISTINCT YEAR(tanggal_mulai) as tahun')
            ->where('tanggal_mulai', '>=', '2000-01-01')
            ->pluck('tahun')
            ->push((int) date('Y'))
            ->unique()->sortDesc()->values();

        $totalPegawai = \App\Models\User::bukanSuperadmin()->count();
        $pengajuanBulanIni = \App\Models\PengajuanCuti::where('approval_step', 8)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();

        // Rata-rata sisa: 1 query (sebelumnya 1 query per pegawai)
        $terpakaiTahun = \App\Models\PengajuanCuti::where('approval_step', 8)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
            ->selectRaw('user_id, SUM(durasi_hari) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $idCutiBesar = \App\Models\PengajuanCuti::where('approval_step', 8)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereHas('jenisCuti', fn ($q) => $q->where('nama_cuti', 'Cuti Besar'))
            ->pluck('user_id')->flip();

        $rataSisa = round(
            \App\Models\User::bukanSuperadmin()->get(['id', 'jatah_cuti', 'saldo_tahun_lalu', 'koreksi_terpakai'])
                ->map(fn ($u) => \App\Models\User::saldoDari(
                    $u, (int) ($terpakaiTahun[$u->id] ?? 0), $idCutiBesar->has($u->id)
                )['total_sisa'])
                ->avg() ?? 0,
            1
        );

        return view('admin.rekap.index', compact(
            'rekaps', 'totalPegawai', 'pengajuanBulanIni', 'rataSisa',
            'daftarDivisi', 'daftarSubBagian', 'daftarJenisCuti', 'daftarTahun',
            'tahun', 'bulan', 'sortBy', 'sortDir'
        ));
    }

        public function showRekap(Request $request, $id)
    {
        $user  = \App\Models\User::with(['bagianBidang', 'subBagianSeksi'])->findOrFail($id);
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) date('Y');

        $tercatat = (int) \App\Models\PengajuanCuti::where('user_id', $user->id)
            ->where('approval_step', 8)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
            ->sum('durasi_hari');

        $saldo = \App\Models\User::saldoDari($user, $tercatat, $user->punyaCutiBesar($tahun));

        $pegawai = (object) [
            'id'     => $user->id,
            'nama'   => $user->name,
            'nip'    => $user->nip,
            'divisi' => $user->subBagianSeksi->nama ?? $user->bagianBidang->nama ?? '-',
        ];

        $riwayats = \App\Models\PengajuanCuti::with('jenisCuti')
            ->where('user_id', $user->id)
            ->whereYear('tanggal_mulai', $tahun)
            ->orderByDesc('tanggal_mulai')
            ->get();

        $ringkasJenis = $riwayats->where('approval_step', 8)
            ->groupBy(fn ($c) => $c->jenisCuti->nama_cuti ?? '-')
            ->map(fn ($g) => ['kali' => $g->count(), 'hari' => $g->sum('durasi_hari')]);

        $daftarTahun = \App\Models\PengajuanCuti::where('user_id', $user->id)
            ->where('tanggal_mulai', '>=', '2000-01-01')
            ->selectRaw('DISTINCT YEAR(tanggal_mulai) as t')->pluck('t')
            ->push((int) date('Y'))->unique()->sortDesc()->values();

        return view('admin.rekap.show', compact(
            'user', 'pegawai', 'saldo', 'riwayats', 'ringkasJenis', 'tahun', 'daftarTahun', 'tercatat'
        ));
    }

        public function exportRekap(Request $request)
    {
        [$tahun, $bulan] = $this->periodeRekapDariRequest($request);
        $jenisCutiId = ($request->filled('jenis_cuti') && $request->jenis_cuti !== 'Semua Jenis')
            ? (int) $request->jenis_cuti
            : null;

        // Ekspor sekarang ikut membawa filter yang sedang aktif di halaman
        // (divisi, sub-bagian, jenis cuti, tahun, bulan, pencarian) - sebelumnya
        // tombol ini selalu mengunduh SEMUA pegawai tanpa filter apapun.
        $export = new \App\Exports\RekapCutiExport(
            search: $request->search,
            divisiId: ($request->filled('divisi') && $request->divisi !== 'Semua Divisi') ? $request->divisi : null,
            subBagianId: ($request->filled('sub_bagian') && $request->sub_bagian !== 'Semua Sub-Bagian') ? $request->sub_bagian : null,
            jenisCutiId: $jenisCutiId,
            tahun: $tahun,
            bulan: $bulan,
        );

        $namaFile = 'Rekap_Cuti_Pegawai_' . $tahun . ($bulan ? '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) : '') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download($export, $namaFile);
    }
        public function updateJatahIndividu(Request $request, $id)
    {
        $data = $request->validate([
            'jatah_cuti'       => 'required|integer|min:0|max:365',
            'saldo_tahun_lalu' => 'required|integer|min:0|max:12',
            'koreksi_terpakai' => 'required|integer|min:-60|max:60',
            'alasan'           => 'required|string|max:255',
        ], ['alasan.required' => 'Alasan koreksi wajib diisi.']);

        $user = \App\Models\User::findOrFail($id);
        $lama = $user->only(['jatah_cuti', 'saldo_tahun_lalu', 'koreksi_terpakai']);

        $user->jatah_cuti       = $data['jatah_cuti'];
        $user->saldo_tahun_lalu = $data['saldo_tahun_lalu'];
        $user->koreksi_terpakai = $data['koreksi_terpakai'];
        $user->save();

        \Illuminate\Support\Facades\Log::info('[Koreksi kuota]', [
            'oleh'    => auth()->user()->name,
            'pegawai' => $user->name,
            'lama'    => $lama,
            'baru'    => collect($data)->only(['jatah_cuti', 'saldo_tahun_lalu', 'koreksi_terpakai'])->all(),
            'alasan'  => $data['alasan'],
        ]);

        return redirect()->route('admin.rekap.show', $id)
            ->with('success', "Kuota cuti {$user->name} berhasil dikoreksi.");
    }
        private function hitungRolloverTahun(int $tahunDitutup)
    {
        $constraint = $this->constraintPengajuanRekap($tahunDitutup, null, null);

        return \App\Models\User::bukanSuperadmin()
            ->withSum(['pengajuanCutis as terpakai_tahun_ini' => function ($q) use ($constraint) {
                $constraint($q);
                $q->whereHas('jenisCuti', fn ($jq) => $jq->where('mengurangi_kuota', true));
            }], 'durasi_hari')
            ->withExists(['pengajuanCutis as punya_cuti_besar' => function ($q) use ($tahunDitutup) {
                $q->where('approval_step', 8)
                  ->whereYear('tanggal_mulai', $tahunDitutup)
                  ->whereHas('jenisCuti', fn ($j) => $j->where('nama_cuti', 'Cuti Besar'));
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $s          = \App\Models\User::saldoDari($user, (int) ($user->terpakai_tahun_ini ?? 0), (bool) $user->punya_cuti_besar);
                $sisaMentah = max(0, $s['total_sisa']);
                $sisaDibawa = min(6, $sisaMentah);

                return (object) [
                    'id'             => $user->id,
                    'nama'           => $user->name,
                    'nip'            => $user->nip,
                    'jatah_saat_ini' => $s['kuota'],
                    'terpakai'       => $s['terpakai'],
                    'sisa_mentah'    => $sisaMentah,
                    'sisa_dibawa'    => $sisaDibawa,
                    'jatah_baru'     => $sisaDibawa + 12,
                ];
            });
    }

    public function previewTutupTahun(Request $request)
    {
        $tahunDitutup = (int) ($request->input('tahun') ?: date('Y'));

        $sudahDitutup = \App\Models\TutupTahunLog::where('tahun_ditutup', $tahunDitutup)->latest()->first();
        $tahunBelumBerakhir = $tahunDitutup >= (int) date('Y') && !(date('Y') > $tahunDitutup);
        $hasil = $this->hitungRolloverTahun($tahunDitutup);

        return view('admin.rekap.tutup-tahun', [
            'tahunDitutup'       => $tahunDitutup,
            'tahunBaru'          => $tahunDitutup + 1,
            'hasil'              => $hasil,
            'sudahDitutup'       => $sudahDitutup,
            'tahunBelumBerakhir' => $tahunBelumBerakhir,
        ]);
    }

    public function prosesTutupTahun(Request $request)
    {
        $request->validate([
            'tahun'       => 'required|integer|min:2020|max:2100',
            'konfirmasi'  => 'required|accepted',
        ]);

        $tahunDitutup = (int) $request->tahun;
        $hasil = $this->hitungRolloverTahun($tahunDitutup);

        \Illuminate\Support\Facades\DB::transaction(function () use ($hasil, $tahunDitutup) {
            foreach ($hasil as $baris) {
                // SESUDAH
        \App\Models\User::whereKey($baris->id)->update([
                'jatah_cuti'       => $baris->jatah_baru,
                'saldo_tahun_lalu' => $baris->sisa_dibawa,
            ]);
            }

                \App\Models\User::whereKey($baris->id)->update([
                    'jatah_cuti'       => $baris->jatah_baru,
                    'saldo_tahun_lalu' => $baris->sisa_dibawa,
                    'koreksi_terpakai' => 0,   // tahun baru mulai dari nol
                ]);
        });

        return redirect()->route('admin.rekap.index')
            ->with('success', "Tutup Tahun {$tahunDitutup} berhasil. Jatah cuti {$hasil->count()} pegawai sudah diperbarui untuk tahun " . ($tahunDitutup + 1) . ".");
    }
    public function dashboardAdmin()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // 1. DATA STATISTIK KARTU
        // Hitung pengajuan yang masuk ke meja Admin (Step 3: Verifikasi Kasubag, Step 7: Finalisasi)
        $pengajuanBaru = \App\Models\PengajuanCuti::whereIn('approval_step', [3, 7])->count();

        // Hitung semua pengajuan yang masih menggantung (belum final)
        $menungguPersetujuan = \App\Models\PengajuanCuti::whereNotIn('approval_step', [8,0,10])->count();

        // Hitung yang disetujui pada bulan ini
        $disetujuiBulanIni = \App\Models\PengajuanCuti::where('approval_step',8)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        // Hitung yang ditolak pada bulan ini
        $ditolakBulanIni = \App\Models\PengajuanCuti::whereIn('approval_step', [0,10])
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        // 2. DATA TABEL (Butuh Tindakan Admin)
        $antreanCuti = \App\Models\PengajuanCuti::with(['user', 'jenisCuti'])
            ->whereIn('approval_step', [3, 7]) // Hanya tampilkan yang butuh klik dari Admin
            ->latest()
            ->paginate(5); // Tampilkan 5 data per halaman

        return view('admin.dashboard', compact(
            'pengajuanBaru', 
            'menungguPersetujuan', 
            'disetujuiBulanIni', 
            'ditolakBulanIni', 
            'antreanCuti'
        ));
    }
    public function informasi()
{
            $user = auth()->user();

    // Catatan: Jika di tabel users belum ada kolom ini, kamu bisa menggunakan angka statis dulu
    // atau nanti kita buatkan file migrasinya.
    $totalJatah = $user->jatah_cuti ?? 12;
    $jatahTahunIni  = 12; // jatah dasar tahun berjalan
    $jatahTahunLalu = max(0, $totalJatah - $jatahTahunIni);
        $terpakai = \App\Models\PengajuanCuti::where('user_id', $user->id)
            ->where('approval_step', 8)
            ->whereYear('tanggal_mulai', now()->year)
            ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
            ->sum('durasi_hari');

        $saldo = \App\Models\User::saldoDari($user, (int) $terpakai, $user->punyaCutiBesar(now()->year));

        $totalJatah     = $saldo['total_sisa'];
        $jatahTahunIni  = $saldo['sisa_berjalan'];
        $jatahTahunLalu = $saldo['sisa_lalu'];

    $statistik = [
        'total_diajukan' => \App\Models\PengajuanCuti::where('user_id', $user->id)->count(),
        'disetujui' => \App\Models\PengajuanCuti::where('user_id', $user->id)
                            ->where('approval_step', 8)->count(),
        'menunggu' => \App\Models\PengajuanCuti::where('user_id', $user->id)
                            ->whereNotIn('approval_step', [0, 8, 10])->count(),
    ];
   if ($user->hasRole('Pegawai')) {
        return view('pegawai.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
    }

    // Jika bukan Pegawai (berarti Kepala Seksi/Bidang), kembalikan ke view kepala
    return view('kepala.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
}
}