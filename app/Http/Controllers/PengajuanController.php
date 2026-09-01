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
            'surat_pengajuan' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            
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
        
        // ========================================================
        // LOGIKA POTONG KOMPAS (HIERARKI 5 LEVEL)
        // ========================================================
        $inisialStep = 1; // Default Pegawai biasa masuk ke Kasi (Step 1)

        if ($user->hasRole('Kepala Seksi') || $user->hasRole('Admin Kepegawaian')) {
            $inisialStep = 2; // Lompat ke Kepala Bidang
        } elseif ($user->hasRole('Kepala Bidang')) {
            $inisialStep = 3; // Lompat ke Kepala Sub Bagian
        } elseif ($user->hasRole('Kepala Sub Bagian')) {
            $inisialStep = 4; // Lompat ke Kepala TU
        } elseif ($user->hasRole('Kepala TU')) {
            $inisialStep = 5; // Lompat ke Kepala Kantor
        } elseif ($user->hasRole('Kepala Kantor')) {
            $inisialStep = 6; // Langsung Finish (Disetujui)
        }

        // Menentukan Teks Status Berdasarkan Hierarki
        $statusPengajuan = 'Menunggu Persetujuan';
        if ($user->hasRole('Kepala Kantor')) {
            $statusPengajuan = 'Disetujui Otomatis (Pimpinan)';
        } elseif ($user->atasan && $user->atasan->roles->isNotEmpty()) {
            $statusPengajuan = 'Menunggu ' . $user->atasan->roles->first()->name;
        } else {
            // Fallback teks jika atasan belum diset di seeder
            $jabatanMap = [
                1 => 'Kepala Seksi',
                2 => 'Kepala Bidang',
                3 => 'Kepala Sub Bagian',
                4 => 'Kepala TU',
                5 => 'Kepala Kantor'
            ];
            $statusPengajuan = 'Menunggu ' . ($jabatanMap[$inisialStep] ?? 'Persetujuan Akhir');
        }

        PengajuanCuti::create([
            'user_id'          => $user->id,
            'jenis_cuti_id'    => $request->jenis_cuti_id,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_selesai'  => $request->tanggal_selesai,
            'alasan'           => $request->alasan,
            'lokasi'           => $request->lokasi,
            'surat_pengajuan'  => $suratPath,
            'bukti_pendukung'  => empty($buktiPaths) ? null : $buktiPaths,
            'approval_step'    => $inisialStep, // Simpan step awal hasil potong kompas
            'status_pengajuan' => $statusPengajuan,
        ]);

        // Mengirim notifikasi ke diri sendiri sebagai konfirmasi
        $user->notify(new StatusCutiNotification(
            'Pengajuan Berhasil Dikirim',
            'Pengajuan cuti Anda untuk tanggal ' . $request->tanggal_mulai . ' telah masuk sistem dan sedang menunggu persetujuan.'
        ));

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
        } elseif ($user->hasRole('Kepala Sub Bagian')) {
            $step = 3;
        } elseif ($user->hasRole('Kepala TU')) {
            $step = 4;
        } elseif ($user->hasRole('Kepala Kantor')) {
            $step = 5;
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

    // 1. MESIN TOMBOL SETUJUI (Update dengan Hierarki 5 Level)
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
}