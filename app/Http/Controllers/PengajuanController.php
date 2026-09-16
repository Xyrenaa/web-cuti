<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\JenisCuti;
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusCutiNotification;

class PengajuanController extends Controller
{
    public function index()
    {
        $riwayat = PengajuanCuti::where('user_id', Auth::id())->latest()->paginate(5);
        $jenisCutis = JenisCuti::all();

        return view('pegawai.pengajuan', compact('riwayat', 'jenisCutis'));
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

    $tanggal_mulai = \Carbon\Carbon::parse($request->tanggal_mulai);
    $tanggal_selesai = \Carbon\Carbon::parse($request->tanggal_selesai);
    

    $durasi_hari = $tanggal_mulai->diffInWeekdays($tanggal_selesai->copy()->addDay());

    // Validasi pencegahan jika user murni mengajukan hanya di hari libur (misal: Sabtu ke Minggu)
    if ($durasi_hari < 1) {
        return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid. Pengajuan cuti tidak bisa dilakukan di hari libur akhir pekan.');
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

    $user = Auth::user();
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
    $lastUrut = (int) substr($lastPengajuan->kode_pengajuan, 2);
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
        // Fungsi dasar tetap: Ambil data milik user yang sedang login
        // (Ditambah with('jenisCuti') agar loading database lebih ringan/cepat)
        $query = PengajuanCuti::where('user_id', Auth::id())->with('jenisCuti')->latest();

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
        $riwayat = $query->paginate(5);
        
        // Ambil data jenis cuti untuk mengisi pilihan di dropdown HTML nanti
        $jenis_cutis = \App\Models\JenisCuti::all();

        return view('pegawai.riwayat', compact('riwayat', 'jenis_cutis')); 
    }
    public function batal($id)
    {
    $pengajuan = \App\Models\PengajuanCuti::find($id);

    if (auth()->id() !== $pengajuan->user_id) {
        abort(403, 'Anda hanya dapat membatalkan pengajuan cuti Anda sendiri.');
    }

    $stepFinal = [0, 8, 9, 10]; // Ditolak, Disetujui, Perlu Revisi, Dibatalkan — sesuaikan kalau "Perlu Revisi" mau tetap boleh dibatalkan

    if (in_array($pengajuan->approval_step, $stepFinal)) {
        return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses final atau sudah ditutup.');
    }

    $pengajuan->update(['approval_step' => 10]);

    return redirect()->back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }   
    public function show($id)
    {
        $pengajuan = PengajuanCuti::where('user_id', Auth::id())->findOrFail($id);
        return view('pegawai.detail', compact('pengajuan'));
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
        $step = 0;

        // Tentukan hak akses meja (step) berdasarkan hierarki 5 level
        if ($user->hasRole('Kepala Seksi')) {
            $step = 1;
        } elseif ($user->hasRole('Kepala Bidang')) {
            $step = 2;
        } elseif ($user->hasRole('Kepala Sub-Bagian')) {
            $step = 4; // Berubah, karena Step 3 milik Admin
        } elseif ($user->hasRole('Kepala TU')) {
            $step = 5; // Berubah, langsung dari Kasubag
        } elseif ($user->hasRole('Kepala Kantor')) {
            $step = 6; // Berubah, langsung dari TU
        }

        $pengajuans = PengajuanCuti::with('user')
            ->where('approval_step', $step)
            ->where('user_id', '!=', $user->id) // Jangan tampilkan pengajuan milik sendiri
            ->latest()
            ->paginate(10);

        return view('kepala.approval.index', compact('pengajuans'));
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
        }

        return view('kepala.approval.show', compact('data'));
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
        if ($user->hasRole('admin')) {
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
    if ($request->status == 'Menunggu') {
        $query->whereNotIn('approval_step', [0, 8, 9, 10]);
    } elseif ($request->status == 'Disetujui') {
        $query->where('approval_step', 8);
    } elseif ($request->status == 'Ditolak') {
        $query->where('approval_step', 0);
    }
}

        // 5. FILTER TANGGAL
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 6. EKSEKUSI AKHIR (Sangat Penting: Harus paginate, BUKAN get)
        $pengajuans = $query->latest()->paginate(10)->withQueryString();

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
                // Admin meneruskan ke Kasubag
                $pengajuan->update(['approval_step' => 4,]);
            } elseif ($pengajuan->approval_step == 7) {
                // Admin finalisasi dan selesai
                $pengajuan->update(['approval_step' => 8,]);
            }
            return redirect()->route('admin.approval.index')->with('success', 'Berkas berhasil diproses.');
            
        } elseif ($action == 'revisi' && $pengajuan->approval_step == 9) {
            $pengajuan->update([
                'approval_step' => 7, 
            ]);
            return redirect()->route('admin.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai. Alasan: ' . $catatan);
            
        } elseif ($action == 'tolak' && $pengajuan->approval_step == 9) {
            $pengajuan->update([
                'approval_step' => 0, 
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
    $pengajuan = \App\Models\PengajuanCuti::find($id);

    if (!$pengajuan) {
        return redirect()->route('kepala.approval.index')->with('success', '[DUMMY MODE] Seolah-olah berhasil disetujui dan diteruskan!');
    }

    $user = Auth::user();

    // Guard: jangan sampai seorang kepala bisa approve pengajuan cuti miliknya sendiri
    // (proteksi tambahan di level aksi, selain fix di store() & indexKepala())
    if ($pengajuan->user_id === $user->id) {
        return redirect()->route('kepala.approval.index')
            ->with('error', 'Anda tidak dapat menyetujui pengajuan cuti Anda sendiri.');
    }

    // role => [step yang boleh dia proses, step tujuan berikutnya]
    $transisi = [
        'Kepala Seksi'      => [1, 2],
        'Kepala Bidang'     => [2, 3],
        'Kepala Sub-Bagian' => [4, 5],
        'Kepala TU'         => [5, 6],
        'Kepala Kantor'     => [6, 7],
    ];

   $stepBerikutnya = null;
    $peranAktif     = null;
    foreach ($transisi as $role => [$stepSekarang, $stepTujuan]) {
        if ($user->hasRole($role) && $pengajuan->approval_step == $stepSekarang) {
            $stepBerikutnya = $stepTujuan;
            $peranAktif     = $role;
            break;
        }
    }

    if ($stepBerikutnya === null) {
        return redirect()->route('kepala.approval.index')
            ->with('error', 'Pengajuan ini bukan lagi di meja Anda, atau sudah diproses pihak lain.');
    }

    $pengajuan->approval_step = $stepBerikutnya;
    $pengajuan->save();

    return redirect()->route('kepala.approval.index')->with('success', 'Pengajuan berhasil disetujui dan diteruskan.');
}
    // 2. MESIN TOMBOL TOLAK
    public function tolakKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::find($id);
        
        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('error', '[DUMMY MODE] Seolah-olah pengajuan ditolak permanen!');
        }

        if ($pengajuan->user_id === Auth::id()) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Anda tidak dapat menolak pengajuan cuti Anda sendiri.');
        }

        $pengajuan->approval_step = 0; 
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('error', 'Pengajuan telah ditolak.');
    }

    // 3. MESIN TOMBOL REVISI
    public function revisiKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::find($id);
        
        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('warning', '[DUMMY MODE] Seolah-olah dikembalikan ke pegawai untuk direvisi!');
        }
        
        if ($pengajuan->user_id === Auth::id()) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Anda tidak dapat mengembalikan pengajuan cuti Anda sendiri untuk direvisi.');
        }

        $pengajuan->approval_step = 9; 
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai untuk direvisi.');
    }

    public function rekapAdmin(Request $request)
    {
        // 1. Ambil query dasar
        $query = \App\Models\User::with(['bagianBidang', 'subBagianSeksi']);

        // 2. SEARCH: Logika Pencarian Nama/NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // 3. FILTER: Berdasarkan Divisi (Bagian/Bidang)
        if ($request->filled('divisi') && $request->divisi !== 'Semua Divisi') {
            $query->where('bagian_bidang_id', $request->divisi);
        }

        // 4. SORTING & ANALITIK: Siapa yang paling sering cuti?
        if ($request->filled('sort') && $request->sort !== 'Terbaru') {
            if ($request->sort == 'Terbanyak') {
                $query->withSum(['pengajuanCutis as total_durasi' => function($q) {
                    $q->where('approval_step',8)->whereYear('created_at', date('Y'));
                }], 'durasi_hari')->orderByDesc('total_durasi');
            } else {
                $jenisId = $request->sort;
                $query->withSum(['pengajuanCutis as total_spesifik' => function($q) use ($jenisId) {
                    $q->where('approval_cuti', 8)
                      ->where('jenis_cuti_id', $jenisId)
                      ->whereYear('created_at', date('Y'));
                }], 'durasi_hari')->orderByDesc('total_spesifik');
            }
        } else {
            $query->latest();
        }

        // 5. Sulap data ke format View (KOLOM SUDAH DISESUAIKAN DENGAN DATABASE)
        $rekaps = $query->paginate(10)->through(function ($user) {
            $terpakai = \App\Models\PengajuanCuti::where('user_id', $user->id)
                ->where('approval_step',8)
                ->whereYear('created_at', date('Y'))
                ->whereHas('jenisCuti', function($query){
                    $query->where('mengurangi_kuota', true);
                })
                ->sum('durasi_hari');

            // Menggunakan kolom jatah_cuti dari database
            $kuota = $user->jatah_cuti ?? 12; 
            $divisi = $user->subBagianSeksi->nama_sub_bagian ?? $user->bagianBidang->nama_bagian ?? '-';

            return (object)[
                'id'       => $user->id,
                'nama'     => $user->name,
                'nip'      => $user->nip,
                'divisi'   => $divisi,
                'kuota'    => $kuota,
                'terpakai' => $terpakai,
                'sisa'     => $kuota - $terpakai 
            ];
        });

        $rekaps->appends(request()->query());

        // 6. Data untuk Dropdown Filter & Kartu Statistik
        $daftarDivisi = \App\Models\BagianBidang::all();
        $daftarJenisCuti = \App\Models\JenisCuti::all();
        
        $totalPegawai = \App\Models\User::count();
        $pengajuanBulanIni = \App\Models\PengajuanCuti::where('approval_step',8)
                                ->whereMonth('created_at', date('m'))
                                ->whereYear('created_at', date('Y'))
                                ->count();
        
        // Logika Rata-rata Sisa Cuti
        $totalSisaKeseluruhan = 0;
        $semuaUser = \App\Models\User::all();
        foreach ($semuaUser as $u) {
            $terpakaiUser = \App\Models\PengajuanCuti::where('user_id', $u->id)
                ->where('approval_step',8)
                ->whereYear('created_at', date('Y'))
                ->whereHas('jenisCuti', function($query){
                    $query->where('mengurangi_kuota', true);
                })
                ->sum('durasi_hari');
                
            $kuotaUser = $u->jatah_cuti ?? 12; 
            $totalSisaKeseluruhan += ($kuotaUser - $terpakaiUser);
        }

        $rataSisa = $totalPegawai > 0 ? round($totalSisaKeseluruhan / $totalPegawai, 1) : 0;

        return view('admin.rekap.index', compact('rekaps', 'totalPegawai', 'pengajuanBulanIni', 'rataSisa', 'daftarDivisi', 'daftarJenisCuti'));
    }

    public function showRekap($id)
    {
        $user = \App\Models\User::with(['bagianBidang', 'subBagianSeksi'])->findOrFail($id);

        $terpakai = \App\Models\PengajuanCuti::where('user_id', $user->id)
            ->where('approval_step',8)
            ->whereYear('created_at', date('Y'))
            ->whereHas('jenisCuti', function($query){
                    $query->where('mengurangi_kuota', true);
                })
            ->sum('durasi_hari');

        // Menggunakan kolom jatah_cuti dari database
        $kuota = $user->jatah_cuti ?? 12;
        $divisi = $user->subBagianSeksi->nama_sub_bagian ?? $user->bagianBidang->nama_bagian ?? '-';

        $pegawai = (object)[
            'id'          => $user->id,
            'nama'        => $user->name,
            'nip'         => $user->nip,
            'divisi'      => $divisi,
            'sisa_cuti'   => $kuota - $terpakai,
            'total_kuota' => $kuota
        ];
        
        $riwayats = \App\Models\PengajuanCuti::with('jenisCuti')
            ->where('user_id', $id)
            ->latest()
            ->get()
            ->map(function ($cuti) {
                return (object)[
                    'id'            => $cuti->id,
                    'jenis'         => $cuti->jenisCuti->nama_cuti ?? 'Cuti Tahunan',
                    'tanggal_mulai' => \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M Y'),
                    'durasi'        => $cuti->durasi_hari . ' Hari',
                    'status'        => $cuti->approval_step
                ];
            });

        return view('admin.rekap.show', compact('pegawai', 'riwayats'));
    }

    public function exportRekap(Request $request)
    {
        // Panggil library Excel untuk mengunduh file
        // Pastikan kamu sudah menjalankan `php artisan make:export RekapCutiExport`
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RekapCutiExport, 'Rekap_Cuti_Pegawai_' . date('Y') . '.xlsx');
    }

    public function indexRekap(Request $request)
    {
        // 1. STATISTIK ATAS
        $totalPegawai = \App\Models\User::count(); 
        
        $pengajuanBulanIni = \App\Models\PengajuanCuti::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();

        // 2. QUERY DAFTAR PEGAWAI BESERTA CUTINYA (Hanya yang disetujui tahun ini)
        $query = \App\Models\User::with(['bagianBidang', 'subBagianSeksi', 'pengajuanCutis' => function($q) {
            $q->where('approval_step',6)
              ->whereYear('tanggal_mulai', now()->year);
        }]);

        // 3. FITUR PENCARIAN (Nama atau NIP)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // 4. EKSEKUSI DATA
        $users = $query->paginate(10)->withQueryString();

        // 5. HITUNG RATA-RATA SISA CUTI
        $totalSisa = 0;
        foreach ($users as $u) {
            $terpakai = $u->pengajuanCutis->sum('durasi_hari');
            $totalSisa += (12 - $terpakai); // Ganti angka 12 jika kamu punya kolom $u->kuota_cuti
        }
        $rataSisa = $users->count() > 0 ? round($totalSisa / $users->count(), 1) : 0;

        return view('admin.rekap.index', compact('totalPegawai', 'pengajuanBulanIni', 'rataSisa', 'users'));
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
        $disetujuiBulanIni = \App\Models\PengajuanCuti::where('approval_step', [8])
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
    $jatahTahunIni = $user->jatah_cuti_tahun_ini ?? 12; 
    $jatahTahunLalu = $user->sisa_cuti_tahun_lalu ?? 2; 
    $totalJatah = $jatahTahunIni + $jatahTahunLalu;

    // Menghitung statistik pengajuan pegawai dari database
    $statistik = [
        'total_diajukan' => \App\Models\PengajuanCuti::where('user_id', $user->id)->count(),
        'disetujui' => \App\Models\PengajuanCuti::where('user_id', $user->id)
                            ->where('approval_step',8)->count(),
        'menunggu' => \App\Models\PengajuanCuti::where('user_id', $user->id)
                            ->where('approval_step', 'LIKE', '%Menunggu%')->count(),
    ];

   if ($user->hasRole('Pegawai')) {
        return view('pegawai.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
    }

    // Jika bukan Pegawai (berarti Kepala Seksi/Bidang), kembalikan ke view kepala
    return view('kepala.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
}
}