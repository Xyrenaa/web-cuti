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
            'jenis_cuti_id'   => 'required|exists:jenis_cutis,id',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'          => 'required|string',
            'lokasi'          => 'required|string|max:255',
            'surat_pengajuan' => 'required|file|mimes:doc,docx|max:5120',
            
            // VALIDASI BARU: Berupa array, maksimal 5 file, per file maks 5MB
            'bukti_pendukung'   => 'nullable|array|max:5',
            'bukti_pendukung.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $suratFile = $request->file('surat_pengajuan');
        $suratName = time() . '_wajib_' . preg_replace('/\s+/', '_', $suratFile->getClientOriginalName());
        $suratPath = $suratFile->storeAs('dokumen/surat_pengajuan', $suratName, 'public');
     
        $buktiPaths = [];
        if ($request->hasFile('bukti_pendukung')) {
            foreach ($request->file('bukti_pendukung') as $key => $file) {
                $buktiName = time() . '_opsi' . $key . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $buktiPaths[] = $file->storeAs('dokumen/bukti_pendukung', $buktiName, 'public');
            }
        }

        $user = Auth::user();
        $statusPengajuan = 'Menunggu Persetujuan';
        
        if ($user->atasan && $user->atasan->roles->isNotEmpty()) {
            $statusPengajuan = 'Menunggu ' . $user->atasan->roles->first()->name;
        }

        PengajuanCuti::create([
            'user_id'          => $user->id,
            'jenis_cuti_id'    => $request->jenis_cuti_id,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'alasan'           => $request->alasan,
            'lokasi'           => $request->lokasi,
            'surat_pengajuan'  => $suratPath,
            'bukti_pendukung'  => empty($buktiPaths) ? null : $buktiPaths, // Disimpan sebagai Array
            'status_pengajuan' => $statusPengajuan,
        ]);

        // Mengirim notifikasi ke diri sendiri sebagai konfirmasi (Bisa juga diganti ke atasan)
        $user->notify(new StatusCutiNotification(
            'Pengajuan Berhasil Dikirim',
            'Pengajuan cuti Anda untuk tanggal ' . $request->tanggal_mulai . ' telah masuk sistem dan sedang menunggu persetujuan.'
        ));

        // Hapus return ganda, cukup satu saja
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
        // Mengambil semua notifikasi milik user yang sedang login
        $notifikasis = Auth::user()->notifications;
        
        // Mengambil jumlah notifikasi yang belum dibaca (untuk badge angka biru)
        $belumDibaca = Auth::user()->unreadNotifications->count();

        return view('pegawai.notifikasi', compact('notifikasis', 'belumDibaca'));
    }
    
    // (TAMBAHAN) Fungsi untuk tombol "Tandai Semua Dibaca"
    public function tandaiSemuaDibaca()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    } 
    // KURUNG TUTUP DI SINI SUDAH DIHAPUS

    public function indexKepala(Request $request)
    {
        $user = Auth::user();
        $step = 0;

        // Tentukan hak akses step berdasarkan role Spatie
        if ($user->hasRole('Kepala Seksi')) {
            $step = 1;
        } elseif ($user->hasRole('Kepala Bidang')) {
            $step = 2;
        } elseif ($user->hasRole('Kepala Sub-Bagian')) {
            $step = 3;
        } elseif ($user->hasRole('Kepala TU')) {
            $step = 4;
        } elseif ($user->hasRole('Kepala Kantor')) {
            $step = 5;
        }

        // Ambil data pengajuan cuti beserta data pegawainya (relasi user)
        $pengajuans = PengajuanCuti::with('user')
            ->where('approval_step', $step)
            ->latest()
            ->paginate(10);

        // Arahkan ke file index di dalam folder admin/approval
        return view('kepala.approval.index', compact('pengajuans'));
    }

    public function showKepala($id)
    {
        // 1. Coba cari data aslinya dulu di database menggunakan find() bukan findOrFail()
        $data = \App\Models\PengajuanCuti::with('user')->find($id);
        
        // 2. JIKA DATA KOSONG, KITA BUAT DATA DUMMY SEMENTARA UNTUK TESTING UI
        if (!$data) {
            $data = new \App\Models\PengajuanCuti();
            $data->id = $id;
            $data->nomor_pengajuan = 'CT.2026.DUMMY-' . $id;
            $data->jenis_cuti = 'Cuti Tahunan (Mode Dummy)';
            $data->durasi_hari = 3;
            $data->tanggal_mulai = now()->addDays(7); // Mulai minggu depan
            $data->created_at = now();
            $data->alasan = 'Ini adalah teks dummy sementara. Sistem tidak menemukan ID ' . $id . ' di database, jadi halaman ini menampilkan data buatan untuk kebutuhan testing desain UI.';
            $data->lampiran = null;
            $data->approval_step = 2; // Ubah angka ini (1-5) untuk ngetes UI warna indikator persetujuan
            $data->status = 'Menunggu';

            // Membuat relasi "user" palsu agar $data->user->name tidak error
            $dummyUser = new \App\Models\User();
            $dummyUser->name = 'Budi Dummy (Tester)';
            $data->setRelation('user', $dummyUser);
        }

        // 3. Kirim data (asli atau dummy) ke halaman view
        return view('kepala.approval.show', compact('data'));
    }

    // =================================================================
    // FUNGSI UNTUK ADMIN KEPEGAWAIAN
    // =================================================================
    
    public function indexApproval()
    {
        // Admin biasanya bisa melihat semua pengajuan cuti dari semua pegawai
        $pengajuans = PengajuanCuti::with('user')
            ->latest()
            ->paginate(10);

        // Mengarahkan ke file view resources/views/admin/approval/index.blade.php
        return view('admin.approval.index', compact('pengajuans'));
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
        $catatan = $request->input('catatan'); // Menangkap alasan dari textarea Modal

        $pengajuan = \App\Models\PengajuanCuti::find($id);

        // =========================================================
        // JIKA MODE DUMMY (Untuk Testing UI)
        // =========================================================
        if (!$pengajuan) {
            if (in_array($id, [1, 2, 3])) { 
                if ($action == 'setujui') {
                    return redirect()->route('admin.approval.index')->with('success', '(Mode Dummy) Berkas berhasil diteruskan.');
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
                $pengajuan->update(['approval_step' => 4, 'status_pengajuan' => 'Menunggu Persetujuan Kasubag']);
            } elseif ($pengajuan->approval_step == 5) {
                $pengajuan->update(['approval_step' => 6, 'status_pengajuan' => 'Menunggu Persetujuan Kepala TU']);
            } elseif ($pengajuan->approval_step == 7) {
                $pengajuan->update(['approval_step' => 8, 'status_pengajuan' => 'Menunggu Persetujuan Kepala Kantor']);
            }
            return redirect()->route('admin.approval.index')->with('success', 'Berkas berhasil diverifikasi dan diteruskan.');
            
        } elseif ($action == 'revisi') {
            $pengajuan->update([
                'approval_step' => 0, 
                'status_pengajuan' => 'Perlu Revisi (Dari Admin)'
                // TIPS: Jika kamu punya kolom 'catatan' di database, hilangkan tanda komen di bawah ini:
                // , 'catatan' => $catatan 
            ]);
            return redirect()->route('admin.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai. Alasan: ' . $catatan);
            
        } elseif ($action == 'tolak') {
            $pengajuan->update([
                'approval_step' => 0, 
                'status_pengajuan' => 'Ditolak'
                // TIPS: Jika kamu punya kolom 'catatan' di database, hilangkan tanda komen di bawah ini:
                // , 'catatan' => $catatan 
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

    public function editAdmin(Request $request)
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    // 1. MESIN TOMBOL SETUJUI
    public function approveKepala(Request $request, $id)
    {
        // Pakai find() biasa, BUKAN findOrFail()
        $pengajuan = \App\Models\PengajuanCuti::find($id);
        
        // CEK DUMMY: Kalau data tidak ada di database, lewati proses save()
        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('success', '[DUMMY MODE] Seolah-olah berhasil disetujui dan diteruskan!');
        }

        $user = Auth::user();
        if ($user->hasRole('Kepala Seksi')) {
            $pengajuan->approval_step = 2;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Sub-Bagian';
        } elseif ($user->hasRole('Kepala Sub-Bagian')) {
            $pengajuan->approval_step = 3;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Bagian';
        } elseif ($user->hasRole('Kepala Bagian')) {
            $pengajuan->approval_step = 4;
            $pengajuan->status_pengajuan = 'Menunggu Kepala Kantor';
        } elseif ($user->hasRole('Kepala Kantor')) {
            $pengajuan->approval_step = 5;
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

    // 3. MESIN TOMBOL REVISI (BARU)
    public function revisiKepala(Request $request, $id)
    {
        $pengajuan = \App\Models\PengajuanCuti::find($id);
        
        if (!$pengajuan) {
            return redirect()->route('kepala.approval.index')->with('warning', '[DUMMY MODE] Seolah-olah dikembalikan ke pegawai untuk direvisi!');
        }
        
        $pengajuan->approval_step = 0; // Dikembalikan ke step awal (Pegawai)
        $pengajuan->status_pengajuan = 'Perlu Revisi';
        
        $pengajuan->save();
        return redirect()->route('kepala.approval.index')->with('warning', 'Berkas dikembalikan ke pegawai untuk direvisi.');
    }
} // INI ADALAH KURUNG PENUTUP KELAS YANG BENAR (Paling Bawah)