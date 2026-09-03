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
    
    // ========================================================
    // LOGIKA PENENTUAN STEP AWAL (MENGGABUNGKAN JALUR TU & BIDANG)
    // ========================================================
    $inisialStep = 1; // Default Pegawai Bidang biasa masuk ke Kasi (Step 1)

    if ($is_tu) {
        // JALUR TATA USAHA: Pegawai TU langsung masuk ke Dashboard Admin (Step 3)
        $inisialStep = 3; 
    } else {
        // JALUR OPERASIONAL BIDANG & POTONG KOMPAS JABATAN TINGGI
        if ($user->hasRole('Kepala Seksi') || $user->hasRole('Admin Kepegawaian')) {
            $inisialStep = 2; // Lompat ke Kepala Bidang
        } elseif ($user->hasRole('Kepala Bidang')) {
            $inisialStep = 3; 
        } elseif ($user->hasRole('Kepala Sub Bagian')) {
            $inisialStep = 4; 
        } elseif ($user->hasRole('Kepala TU')) {
            $inisialStep = 5; 
        } elseif ($user->hasRole('Kepala Kantor')) {
            $inisialStep = 6; 
        }
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
    PengajuanCuti::create([
        'user_id'           => $user->id,
        'jenis_cuti_id'     => $request->jenis_cuti_id,
        'tanggal_mulai'     => $request->tanggal_mulai,
        'tanggal_selesai'   => $request->tanggal_selesai,
        'alasan'            => $request->alasan,
        'lokasi'            => $request->lokasi,
        'surat_pengajuan'   => $suratPath,
        'bukti_pendukung'   => empty($buktiPaths) ? null : $buktiPaths,
        'approval_step'     => $inisialStep, 
        'status_pengajuan'  => $statusPengajuan,
    ]);

    // 4. Kirim Notifikasi ke User
    if (method_exists($user, 'notify')) {
        $user->notify(new StatusCutiNotification(
            'Pengajuan Berhasil Dikirim',
            'Pengajuan cuti Anda untuk tanggal ' . $request->tanggal_mulai . ' telah masuk sistem dan sedang diproses.'
        ));
    }

    return redirect()->route('pengajuan.index')->with('success', 'Pengajuan cuti dan dokumen lampiran berhasil dikirim.');
}

    public function riwayat()
    {
        $query = PengajuanCuti::where('user_id', Auth::id())->latest();

        if (request()->has('cari') && request()->cari != '') {
            $query->where('alasan', 'like', '%' . request()->cari . '%');
        }
        
        $riwayat = $query->paginate(5);
        return view('pegawai.riwayat', compact('riwayat')); 
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
    
    public function indexApprovals()
{
    $user = Auth::user();
    $query = PengajuanCuti::with(['user.bagianBidang', 'user.subBagianSeksi']);

    if ($user->hasRole('admin')) {
        // ADMIN melihat Step 3 (Verifikasi Awal) DAN Step 7 (Penomoran Surat)
        $pengajuans = $query->whereIn('approval_step', [3, 7])->get();

    } elseif ($user->level_jabatan == 'Kepala Seksi/Sub-Bagian') {
        if ($user->bagianBidang->is_tu) {
            // Kasubag TU: Melihat Step 4 (Pengajuan yang sudah dilewati Admin)
            $pengajuans = $query->where('approval_step', 4)->get();
        } else {
            // Kepala Seksi: Melihat Step 1 (Hanya dari bawahannya sendiri)
            $pengajuans = $query->where('approval_step', 1)
                                ->whereHas('user', function($q) use ($user) {
                                    $q->where('sub_bagian_seksi_id', $user->sub_bagian_seksi_id);
                                })->get();
        }

    } elseif ($user->level_jabatan == 'Kepala Bagian/Bidang') {
        if ($user->bagianBidang->is_tu) {
            // Kepala Bagian TU: Melihat Step 5
            $pengajuans = $query->where('approval_step', 5)->get();
        } else {
            // Kepala Bidang: Melihat Step 2 (Hanya dari bawahannya sendiri)
            $pengajuans = $query->where('approval_step', 2)
                                ->whereHas('user', function($q) use ($user) {
                                    $q->where('bagian_bidang_id', $user->bagian_bidang_id);
                                })->get();
        }

    } elseif ($user->level_jabatan == 'Kepala Kantor') {
        // Kepala Kantor: Melihat Step 6 (Final Approval)
        $pengajuans = $query->where('approval_step', 6)->get();
    }

    return view('admin.approval.index', compact('pengajuans'));
}

public function approve($id)
{
    $pengajuan = PengajuanCuti::findOrFail($id);
    $user = Auth::user();

    // Jika Admin melakukan penomoran surat (Langkah 7 ke 8)
    if ($pengajuan->approval_step == 7 && $user->hasRole('admin')) {
        $pengajuan->approval_step = 8;
        $pengajuan->status = 'Disetujui'; // Status akhir
        // Di sini bisa ditambahkan logika mengurangi jatah_cuti pegawai
        $pegawai = $pengajuan->user;
        $pegawai->jatah_cuti -= $pengajuan->lama_cuti;
        $pegawai->save();
    } 
    // Langkah 1 sampai 6 (Approve normal)
    else {
        $pengajuan->approval_step += 1;
        // Status tetap 'Menunggu Persetujuan' karena belum final
    }

    $pengajuan->save();
    return back()->with('success', 'Pengajuan berhasil diteruskan ke tahap selanjutnya.');
}

    public function showApproval($id)
    {
        // 1. Coba cari data asli di database terlebih dahulu
        $data = \App\Models\PengajuanCuti::with(['user', 'jenisCuti'])->find($id);
        
        // 2. Jika data tidak ada, gunakan data dummy berdasarkan desain dashboard
        if (!$data) {
            $data = new \App\Models\PengajuanCuti();
            $data->id = $id;
            
            $dummyUser = new \App\Models\User();
            $dummyJenisCuti = new \App\Models\JenisCuti();
            
            // Pengkondisian berdasarkan ID pada URL (1, 2, atau 3)
            if ($id == 1) {
                $dummyUser->name = 'Amiruddin Syah';
                $dummyUser->nip = '198804122015031002';
                $dummyJenisCuti->nama_cuti = 'Cuti Tahunan';
                
                $data->created_at = \Carbon\Carbon::parse('2023-10-24');
                $data->tanggal_mulai = \Carbon\Carbon::parse('2023-10-25')->format('Y-m-d');
                $data->tanggal_selesai = \Carbon\Carbon::parse('2023-10-25')->addDays(4)->format('Y-m-d'); // Total 5 Hari
                $data->status_pengajuan = 'Menunggu';

                $data->approval_step = 3;

            } elseif ($id == 2) {
                $dummyUser->name = 'Novianti Rahayu';
                $dummyUser->nip = '198804122015031002';
                $dummyJenisCuti->nama_cuti = 'Cuti Melahirkan';
                
                $data->created_at = \Carbon\Carbon::parse('2023-10-23');
                $data->tanggal_mulai = \Carbon\Carbon::parse('2023-10-24')->format('Y-m-d');
                $data->tanggal_selesai = \Carbon\Carbon::parse('2023-10-24')->addDays(89)->format('Y-m-d'); // Total 90 Hari
                $data->status_pengajuan = 'Disetujui';

                $data->approval_step = 5;

            } elseif ($id == 3) {
                $dummyUser->name = 'Rian Hidayat';
                $dummyUser->nip = '198804122015031002';
                $dummyJenisCuti->nama_cuti = 'Cuti Besar';
                
                $data->created_at = \Carbon\Carbon::parse('2023-10-20');
                $data->tanggal_mulai = \Carbon\Carbon::parse('2023-10-21')->format('Y-m-d');
                $data->tanggal_selesai = \Carbon\Carbon::parse('2023-10-21')->addDays(11)->format('Y-m-d'); // Total 12 Hari
                $data->status_pengajuan = 'Ditolak';

                $data->approval_step = 7;

            } else {
                // Fallback jika mengetik ID selain 1, 2, 3 di URL
                $dummyUser->name = 'Pegawai Tidak Dikenal';
                $dummyUser->nip = '000000000000000000';
                $dummyJenisCuti->nama_cuti = 'Cuti Tahunan';
                $data->created_at = now();
                $data->tanggal_mulai = now()->format('Y-m-d');
                $data->tanggal_selesai = now()->addDays(2)->format('Y-m-d');
                $data->status_pengajuan = 'Menunggu';
            }

            // Data pelengkap agar halaman view tidak error karena variabel kosong
            $dummyUser->jabatan = 'Staf Operasional';
            $data->alasan = 'Teks alasan ini merupakan data dummy yang digunakan untuk kebutuhan testing desain UI halaman persetujuan.';
            $data->lokasi = 'Surabaya'; 
            $data->surat_pengajuan = 'dokumen/dummy_surat.pdf';
            $data->bukti_pendukung = ['dokumen/dummy_bukti1.jpg']; // Format array karena di fungsi store() kamu setting array
            
            // Menggabungkan relasi buatan ke data utama
            $data->setRelation('user', $dummyUser);
            $data->setRelation('jenisCuti', $dummyJenisCuti);
        }
        
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
                $pengajuan->update(['approval_step' => 4, 'status_pengajuan' => 'Menunggu Persetujuan Kasubag']);
            } elseif ($pengajuan->approval_step == 7) {
                // Admin finalisasi dan selesai
                $pengajuan->update(['approval_step' => 8, 'status_pengajuan' => 'Disetujui']);
            }
            return redirect()->route('admin.approval.index')->with('success', 'Berkas berhasil diproses.');
            
        } elseif ($action == 'revisi' && $pengajuan->approval_step == 7) {
            $pengajuan->update([
                'approval_step' => 0, 
                'status_pengajuan' => 'Perlu Revisi (Dari Admin Akhir)'
            ]);
            return redirect()->route('admin.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai. Alasan: ' . $catatan);
            
        } elseif ($action == 'tolak' && $pengajuan->approval_step == 7) {
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
        
        // Operan antar meja berdasarkan hierarki
        if ($user->hasRole('Kepala Seksi')) {
            $pengajuan->approval_step = 2;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Bidang';
        } elseif ($user->hasRole('Kepala Bidang')) {
            $pengajuan->approval_step = 3;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Sub Bagian';
        } elseif ($user->hasRole('Kepala Sub-Bagian')) {
            $pengajuan->approval_step = 4;
            $pengajuan->status_pengajuan = 'Menunggu Kepala TU';
        } elseif ($user->hasRole('Kepala TU')) {
            $pengajuan->approval_step = 5;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Kantor';
        } elseif ($user->hasRole('Kepala Kantor')) {
            $pengajuan->approval_step = 6; 
            $pengajuan->status_pengajuan = 'Disetujui';
        }

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
        
        $pengajuan->approval_step = 0; 
        $pengajuan->status_pengajuan = 'Ditolak';
        
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
        
        $pengajuan->approval_step = 0; 
        $pengajuan->status_pengajuan = 'Perlu Revisi';
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai untuk direvisi.');
    }

    public function rekapAdmin(Request $request)
    {
        // DATA DUMMY UNTUK TESTING DESAIN UI REKAPITULASI
        $rekaps = [
            (object)[
                'id' => 1, 'nama' => 'Ahmad Subarjo', 'nip' => '198804122015031002', 
                'divisi' => 'Subbagian Tata Usaha', 'kuota' => 12, 'terpakai' => 3, 'sisa' => 9
            ],
            (object)[
                'id' => 2, 'nama' => 'Siti Rahmawati', 'nip' => '199211082018012005', 
                'divisi' => 'Seksi Keamanan Penerbangan', 'kuota' => 12, 'terpakai' => 2, 'sisa' => 10
            ],
            (object)[
                'id' => 3, 'nama' => 'Budi Kurniawan', 'nip' => '198501252010031001', 
                'divisi' => 'Seksi Operasi Bandar Udara', 'kuota' => 12, 'terpakai' => 12, 'sisa' => 0
            ],
            (object)[
                'id' => 4, 'nama' => 'Dewi Lestari', 'nip' => '199507192020122003', 
                'divisi' => 'Subbagian Hukum & Humas', 'kuota' => 12, 'terpakai' => 5, 'sisa' => 7
            ],
            (object)[
                'id' => 5, 'nama' => 'Hendra Wijaya', 'nip' => '199009022016041004', 
                'divisi' => 'Seksi Kalibrasi Fasilitas', 'kuota' => 12, 'terpakai' => 4, 'sisa' => 8
            ],
        ];

        return view('admin.rekap.index', compact('rekaps'));
    }

    public function exportRekap(Request $request)
    {
        // Nanti diisi dengan logika Maatwebsite Excel
        return back()->with('success', '(Mode Dummy) Rekap berhasil diekspor ke Excel!');
    }

    public function showRekap($id)
    {
        // DATA DUMMY: Profil Pegawai
        $pegawai = (object)[
            'id' => $id,
            'nama' => 'Ahmad Subarjo',
            'nip' => '198804122015031002',
            'divisi' => 'Subbagian Tata Usaha',
            'sisa_cuti' => 9,
            'total_kuota' => 12
        ];
        
        // DATA DUMMY: Riwayat Pengambilan Cuti
        $riwayats = [
            (object)['jenis' => 'Cuti Tahunan', 'tanggal_mulai' => '15 Mar 2026', 'durasi' => '3 Hari', 'status' => 'Selesai'],
            (object)['jenis' => 'Cuti Sakit', 'tanggal_mulai' => '02 Feb 2026', 'durasi' => '1 Hari', 'status' => 'Selesai'],
        ];

        return view('admin.rekap.show', compact('pegawai', 'riwayats'));
    }
} // INI ADALAH KURUNG PENUTUP KELAS YANG BENAR (Paling Bawah)
