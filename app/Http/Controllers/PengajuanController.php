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

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan cuti dan dokumen lampiran berhasil dikirim.');

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan cuti dan dokumen lampiran berhasil dikirim.');
    }
    public function riwayat()
    {
        $query = PengajuanCuti::where('user_id', Auth::id())->latest();

        if (request()->has('cari') && request()->cari != '') {
            $query->where('alasan', 'like', '%' . request()->cari . '%');
        }
        
        // 1. PINDAHKAN KODE INI KE DALAM SINI
        $riwayat = $query->paginate(5);
        return view('pegawai.riwayat', compact('riwayat')); 
        // Catatan: Pastikan nama file blade-mu adalah 'riwayat.blade.php'
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
} 

    public function indexKepala(Request $request)
    {
        $user = Auth::user();
        $step = 0;

        // Tentukan hak akses step berdasarkan role Spatie
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
        // Ambil data pengajuan beserta data pegawainya berdasarkan ID
        $data = \App\Models\PengajuanCuti::with('user')->findOrFail($id);
        if (!$data) {
            return redirect()->route('kepala.approval.index')->with('error', 'Data pengajuan tidak ditemukan!');
        }
        return view('kepala.approval.show', compact('data'));
    }
}
