<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Models\JenisCuti;
use App\Models\User;
use App\Models\BagianBidang;
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusCutiNotification;
use Carbon\Carbon;

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

        $tanggal_mulai = Carbon::parse($request->tanggal_mulai);
        $tanggal_selesai = Carbon::parse($request->tanggal_selesai);

        $durasi_hari = $tanggal_mulai->diffInWeekdays($tanggal_selesai->copy()->addDay());

        if ($durasi_hari < 1) {
            return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid. Pengajuan cuti tidak bisa dilakukan di hari libur akhir pekan.');
        }

        $cutiAktifMasihBerjalan = PengajuanCuti::where('user_id', Auth::id())
            ->where('approval_step', 8)
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->exists();

        if ($cutiAktifMasihBerjalan) {
            return redirect()->back()->withInput()->with('error', 'Anda masih memiliki cuti yang sudah disetujui dan periodenya belum selesai.');
        }

        // 1. Upload Berkas Surat Pengajuan
        $suratFile = $request->file('surat_pengajuan');
        $suratName = time() . '_wajib_' . preg_replace('/\s+/', '_', $suratFile->getClientOriginalName());
        $suratPath = $suratFile->storeAs('dokumen/surat_pengajuan', $suratName, 'public');
     
        // 2. Upload Berkas Bukti Pendukung
        $buktiPaths = [];
        if ($request->hasFile('bukti_pendukung')) {
            foreach ($request->file('bukti_pendukung') as $key => $file) {
                $buktiName = time() . '_opsi' . $key . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $buktiPaths[] = $file->storeAs('dokumen/bukti_pendukung', $buktiName, 'public');
            }
        }

        $user = Auth::user();
        $is_tu = $user->bagianBidang ? $user->bagianBidang->is_tu : false;
        
        $inisialStep = 1; // Default Pegawai Bidang biasa -> Kasi (Step 1)

        if ($is_tu) {
            $inisialStep = 3; // TU -> Direct to Admin (Step 3)
        } else {
            // Penyesuaian nama Role diseragamkan dengan strip ('Kepala Sub-Bagian')
            if ($user->hasRole('Kepala Seksi') || $user->hasRole('Admin Kepegawaian')) {
                $inisialStep = 2;
            } elseif ($user->hasRole('Kepala Bidang')) {
                $inisialStep = 3; 
            } elseif ($user->hasRole('Kepala Sub-Bagian') || $user->hasRole('Kasubag')) {
                $inisialStep = 4; 
            } elseif ($user->hasRole('Kepala TU')) {
                $inisialStep = 5; 
            } elseif ($user->hasRole('Kepala Kantor')) {
                $inisialStep = 6; 
            }
        }

        $statusPengajuan = 'Menunggu Persetujuan';
        if ($user->hasRole('Kepala Kantor')) {
            $statusPengajuan = 'Disetujui Otomatis (Pimpinan)';
        } else {
            $jabatanMap = [
                1 => 'Menunggu Kepala Seksi',
                2 => 'Menunggu Kepala Bidang',
                3 => 'Menunggu Verifikasi Admin',
                4 => 'Menunggu Kepala Sub-Bagian',
                5 => 'Menunggu Kepala TU',
                6 => 'Menunggu Kepala Kantor'
            ];
            $statusPengajuan = $jabatanMap[$inisialStep] ?? 'Menunggu Persetujuan';
        }

        // 3. Generate Kode Pengajuan (PERBAIKAN: jenis_cuti -> jenis_cuti_id)
        $jenisCuti = JenisCuti::find($request->jenis_cuti_id);
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

        // Perbaikan di sini: Menggunakan $request->jenis_cuti_id
        $lastPengajuan = PengajuanCuti::where('jenis_cuti_id', $request->jenis_cuti_id)
            ->orderBy('id', 'desc')
            ->first();

        $nomorUrut = 1;
        if ($lastPengajuan && $lastPengajuan->kode_pengajuan) {
            $lastUrut = (int) preg_replace('/[^0-9]/', '', $lastPengajuan->kode_pengajuan);
            $nomorUrut = $lastUrut + 1;
        }

        $kodeBaru = $prefix . str_pad($nomorUrut, 2, '0', STR_PAD_LEFT);

        PengajuanCuti::create([
            'kode_pengajuan'    => $kodeBaru,
            'user_id'           => $user->id,
            'jenis_cuti_id'     => $request->jenis_cuti_id,
            'tanggal_mulai'     => $request->tanggal_mulai,
            'tanggal_selesai'   => $request->tanggal_selesai,
            'durasi_hari'       => $durasi_hari,
            'alasan'            => $request->alasan,
            'lokasi'            => $request->lokasi,
            'surat_pengajuan'   => $suratPath,
            'bukti_pendukung'   => empty($buktiPaths) ? null : $buktiPaths,
            'approval_step'     => $inisialStep, 
            'status_pengajuan'  => $statusPengajuan,
        ]);

        if (method_exists($user, 'notify')) {
            $user->notify(new StatusCutiNotification(
                'Pengajuan Berhasil Dikirim',
                'Pengajuan cuti Anda untuk tanggal ' . $request->tanggal_mulai . ' telah masuk sistem dan sedang diproses.'
            ));
        }

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan cuti dan dokumen lampiran berhasil dikirim.');
    }

    public function riwayat(Request $request)
    {
        $query = PengajuanCuti::where('user_id', Auth::id())->with('jenisCuti')->latest();

        if ($request->filled('cari')) {
            $query->where('alasan', 'like', '%' . $request->cari . '%');
        }

        if ($request->filled('jenis_cuti')) {
            $query->where('jenis_cuti_id', $request->jenis_cuti);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai', $request->bulan);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        $riwayat = $query->paginate(5);
        $jenis_cutis = JenisCuti::all();

        return view('pegawai.riwayat', compact('riwayat', 'jenis_cutis')); 
    }

    public function batal($id)
    {
        $pengajuan = PengajuanCuti::findOrFail($id);

        if (auth()->id() !== $pengajuan->user_id) {
            abort(403, 'Anda hanya dapat membatalkan pengajuan cuti Anda sendiri.');
        }

        $statusFinal = ['Selesai', 'Ditolak', 'Dibatalkan'];
        foreach ($statusFinal as $sf) {
            if (stripos($pengajuan->status_pengajuan, $sf) !== false) {
                return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses final atau sudah ditutup.');
            }
        }

        $pengajuan->update([
            'status_pengajuan' => 'Dibatalkan',
        ]);

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

        if ($user->hasRole('Kepala Seksi')) {
            $step = 1;
        } elseif ($user->hasRole('Kepala Bidang')) {
            $step = 2;
        } elseif ($user->hasRole('Kepala Sub-Bagian') || $user->hasRole('Kasubag')) {
            $step = 4;
        } elseif ($user->hasRole('Kepala TU')) {
            $step = 5;
        } elseif ($user->hasRole('Kepala Kantor')) {
            $step = 6;
        }

        $pengajuans = PengajuanCuti::with('user')
            ->where('approval_step', $step)
            ->latest()
            ->paginate(10);

        return view('kepala.approval.index', compact('pengajuans'));
    }

    public function showKepala($id)
    {
        $data = PengajuanCuti::with('user')->find($id);

        return view('kepala.approval.show', compact('data'));
    }

    public function indexApproval(Request $request)
    {
        $user = Auth::user();
        $query = PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi', 'jenisCuti']);

        if ($user->hasRole('admin')) {
            // Admin dapat melihat seluruh data
        } elseif ($user->hasRole('Kepala Seksi') || $user->hasRole('Kepala Sub-Bagian')) {
            if ($user->bagianBidang && $user->bagianBidang->is_tu) {
                $query->where('approval_step', 4);
            } else {
                $query->where('approval_step', 1)->whereHas('user', function($q) use ($user) {
                    $q->where('sub_bagian_seksi_id', $user->sub_bagian_seksi_id);
                });
            }
        } elseif ($user->hasRole('Kepala Bidang')) {
            if ($user->bagianBidang && $user->bagianBidang->is_tu) {
                $query->where('approval_step', 5);
            } else {
                $query->where('approval_step', 2)->whereHas('user', function($q) use ($user) {
                    $q->where('bagian_bidang_id', $user->bagian_bidang_id);
                });
            }
        } elseif ($user->hasRole('Kepala Kantor')) {
            $query->where('approval_step', 6);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'Semua Status') {
            $query->where('status_pengajuan', 'like', '%' . trim($request->status) . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $pengajuans = $query->latest()->paginate(10)->withQueryString();

        return view('admin.approval.index', compact('pengajuans'));
    }

    public function approve($id)
    {
        $pengajuan = PengajuanCuti::findOrFail($id);
        $user = Auth::user();

        // PERBAIKAN: Menggunakan status_pengajuan dan durasi_hari
        if ($pengajuan->approval_step == 7 && $user->hasRole('admin')) {
            $pengajuan->approval_step = 8;
            $pengajuan->status_pengajuan = 'Disetujui';
            
            $pegawai = $pengajuan->user;
            if ($pegawai && isset($pegawai->jatah_cuti)) {
                $pegawai->jatah_cuti -= $pengajuan->durasi_hari;
                $pegawai->save();
            }
        } else {
            $pengajuan->approval_step += 1;
        }

        $pengajuan->save();
        return back()->with('success', 'Pengajuan berhasil diteruskan ke tahap selanjutnya.');
    }

    public function showApproval($id)
    {
        $data = PengajuanCuti::with(['user', 'jenisCuti'])->findOrFail($id);
        return view('admin.approval.show', compact('data'));
    }

    public function verifikasiAdmin(Request $request, $id)
    {
        $action = $request->input('action');
        $catatan = $request->input('catatan'); 

        $pengajuan = PengajuanCuti::findOrFail($id);

        if ($action == 'setujui') {
            if ($pengajuan->approval_step == 3) {
                $rolePengaju = $pengajuan->user->role ?? 'Pegawai';

                if ($rolePengaju == 'Kepala Sub-Bagian' || $rolePengaju == 'Kasubag') {
                    $nextStep = 5; 
                    $nextStatus = 'Menunggu Kepala TU';
                } elseif ($rolePengaju == 'Kepala TU') {
                    $nextStep = 6; 
                    $nextStatus = 'Menunggu Kepala Kantor';
                } else {
                    $nextStep = 4;
                    $nextStatus = 'Menunggu Kepala Sub-Bagian';
                }

                $pengajuan->update([
                    'approval_step' => $nextStep, 
                    'status_pengajuan' => $nextStatus
                ]);

            } elseif ($pengajuan->approval_step == 7) {
                $pengajuan->update([
                    'approval_step' => 8, 
                    'status_pengajuan' => 'Disetujui'
                ]);

                // Pengurangan jatah cuti pegawai
                $pegawai = $pengajuan->user;
                if ($pegawai && isset($pegawai->jatah_cuti)) {
                    $pegawai->jatah_cuti -= $pengajuan->durasi_hari;
                    $pegawai->save();
                }
            }
            
            return redirect()->route('admin.approval.index')->with('success', 'Berkas berhasil diproses.');
            
        } elseif ($action == 'revisi') {
            $pengajuan->update([
                'approval_step' => 0, 
                'status_pengajuan' => 'Perlu Revisi'
            ]);
            return redirect()->route('admin.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai. Alasan: ' . $catatan);
            
        } elseif ($action == 'tolak') {
            $pengajuan->update([
                'approval_step' => 0, 
                'status_pengajuan' => 'Ditolak'
            ]);
            return redirect()->route('admin.approval.index')->with('error', 'Berkas pengajuan cuti ditolak. Alasan: ' . $catatan);
        }
    }
    
    public function notifikasiAdmin()
    {
        $notifikasis = Auth::user()->notifications;
        $belumDibaca = Auth::user()->unreadNotifications->count();

        return view('admin.notifikasi', compact('notifikasis', 'belumDibaca'));
    }
    
    public function approveKepala(Request $request, $id)
    {
        $pengajuan = PengajuanCuti::findOrFail($id);
        $user = Auth::user();

        // Penyeragaman label status dan role
        $transisi = [
            'Kepala Seksi'      => [1, 2, 'Menunggu Kepala Bidang'],
            'Kepala Bidang'     => [2, 3, 'Menunggu Verifikasi Admin'],
            'Kepala Sub-Bagian' => [4, 5, 'Menunggu Kepala TU'],
            'Kasubag'           => [4, 5, 'Menunggu Kepala TU'],
            'Kepala TU'         => [5, 6, 'Menunggu Kepala Kantor'],
            'Kepala Kantor'     => [6, 7, 'Menunggu Finalisasi Penomoran'],
        ];

        $cocok = false;
        foreach ($transisi as $peran => [$stepSekarang, $stepTujuan, $labelTujuan]) {
            if ($user->hasRole($peran) && $pengajuan->approval_step == $stepSekarang) {
                $pengajuan->approval_step = $stepTujuan;
                $pengajuan->status_pengajuan = $labelTujuan;
                $cocok = true;
                break;
            }
        }

        if (!$cocok) {
            return redirect()->route('kepala.approval.index')
                ->with('error', 'Pengajuan ini bukan lagi di meja Anda, atau sudah diproses pihak lain.');
        }

        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('success', 'Pengajuan berhasil disetujui dan diteruskan.');
    }

    public function tolakKepala(Request $request, $id)
    {
        $pengajuan = PengajuanCuti::findOrFail($id);
        
        $pengajuan->approval_step = 0; 
        $pengajuan->status_pengajuan = 'Ditolak';
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('error', 'Pengajuan telah ditolak.');
    }

    public function revisiKepala(Request $request, $id)
    {
        $pengajuan = PengajuanCuti::findOrFail($id);
        
        $pengajuan->approval_step = 0; 
        $pengajuan->status_pengajuan = 'Perlu Revisi';
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai untuk direvisi.');
    }

    public function rekapAdmin(Request $request)
    {
        $query = User::with(['bagianBidang', 'subBagianSeksi']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('divisi') && $request->divisi !== 'Semua Divisi') {
            $query->where('bagian_bidang_id', $request->divisi);
        }

        if ($request->filled('sort') && $request->sort !== 'Terbaru') {
            if ($request->sort == 'Terbanyak') {
                $query->withSum(['pengajuanCutis as total_durasi' => function($q) {
                    $q->where('status_pengajuan', 'Disetujui')->whereYear('created_at', date('Y'));
                }], 'durasi_hari')->orderByDesc('total_durasi');
            } else {
                $jenisId = $request->sort;
                $query->withSum(['pengajuanCutis as total_spesifik' => function($q) use ($jenisId) {
                    $q->where('status_pengajuan', 'Disetujui')
                      ->where('jenis_cuti_id', $jenisId)
                      ->whereYear('created_at', date('Y'));
                }], 'durasi_hari')->orderByDesc('total_spesifik');
            }
        } else {
            $query->latest();
        }

        $rekaps = $query->paginate(10)->through(function ($user) {
            $terpakai = PengajuanCuti::where('user_id', $user->id)
                ->where('status_pengajuan', 'Disetujui')
                ->whereYear('created_at', date('Y'))
                ->sum('durasi_hari');

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

        $daftarDivisi = BagianBidang::all();
        $daftarJenisCuti = JenisCuti::all();
        
        $totalPegawai = User::count();
        $pengajuanBulanIni = PengajuanCuti::where('status_pengajuan', 'Disetujui')
                                ->whereMonth('created_at', date('m'))
                                ->whereYear('created_at', date('Y'))
                                ->count();
        
        $totalSisaKeseluruhan = 0;
        $semuaUser = User::all();
        foreach ($semuaUser as $u) {
            $terpakaiUser = PengajuanCuti::where('user_id', $u->id)
                ->where('status_pengajuan', 'Disetujui')
                ->whereYear('created_at', date('Y'))
                ->sum('durasi_hari');
                
            $kuotaUser = $u->jatah_cuti ?? 12; 
            $totalSisaKeseluruhan += ($kuotaUser - $terpakaiUser);
        }

        $rataSisa = $totalPegawai > 0 ? round($totalSisaKeseluruhan / $totalPegawai, 1) : 0;

        return view('admin.rekap.index', compact('rekaps', 'totalPegawai', 'pengajuanBulanIni', 'rataSisa', 'daftarDivisi', 'daftarJenisCuti'));
    }

    public function showRekap($id)
    {
        $user = User::with(['bagianBidang', 'subBagianSeksi'])->findOrFail($id);

        $terpakai = PengajuanCuti::where('user_id', $user->id)
            ->where('status_pengajuan', 'Disetujui')
            ->whereYear('created_at', date('Y'))
            ->sum('durasi_hari');

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
        
        $riwayats = PengajuanCuti::with('jenisCuti')
            ->where('user_id', $id)
            ->latest()
            ->get()
            ->map(function ($cuti) {
                return (object)[
                    'id'            => $cuti->id,
                    'jenis'         => $cuti->jenisCuti->nama_cuti ?? 'Cuti Tahunan',
                    'tanggal_mulai' => Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d M Y'),
                    'durasi'        => $cuti->durasi_hari . ' Hari',
                    'status'        => $cuti->status_pengajuan
                ];
            });

        return view('admin.rekap.show', compact('pegawai', 'riwayats'));
    }

    public function exportRekap(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RekapCutiExport, 'Rekap_Cuti_Pegawai_' . date('Y') . '.xlsx');
    }

    public function dashboardAdmin()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $pengajuanBaru = PengajuanCuti::whereIn('approval_step', [3, 7])->count();
        $menungguPersetujuan = PengajuanCuti::whereNotIn('status_pengajuan', ['Disetujui', 'Ditolak', 'Dibatalkan'])->count();

        $disetujuiBulanIni = PengajuanCuti::where('status_pengajuan', 'Disetujui')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        $ditolakBulanIni = PengajuanCuti::whereIn('status_pengajuan', ['Ditolak', 'Dibatalkan'])
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        $antreanCuti = PengajuanCuti::with(['user', 'jenisCuti'])
            ->whereIn('approval_step', [3, 7])
            ->latest()
            ->paginate(5);

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

        $jatahTahunIni = $user->jatah_cuti_tahun_ini ?? 12; 
        $jatahTahunLalu = $user->sisa_cuti_tahun_lalu ?? 2; 
        $totalJatah = $jatahTahunIni + $jatahTahunLalu;

        $statistik = [
            'total_diajukan' => PengajuanCuti::where('user_id', $user->id)->count(),
            'disetujui'      => PengajuanCuti::where('user_id', $user->id)->where('status_pengajuan', 'Disetujui')->count(),
            'menunggu'       => PengajuanCuti::where('user_id', $user->id)->where('status_pengajuan', 'LIKE', '%Menunggu%')->count(),
        ];

        if ($user->hasRole('Pegawai')) {
            return view('pegawai.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
        }

        return view('kepala.informasi', compact('jatahTahunIni', 'jatahTahunLalu', 'totalJatah', 'statistik'));
    }
}