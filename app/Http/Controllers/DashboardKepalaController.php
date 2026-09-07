<?php

namespace App\Http\Controllers;

use App\Models\PengajuanCuti;
use App\Models\User;
use App\Models\BagianBidang;
use App\Models\SubBagianSeksi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardKepalaController extends Controller
{
    private $kolomStatus = 'status_pengajuan'; 
    private $kolomNamaDivisi = 'nama'; // Atau 'nama_bagian'
    private $fkDivisi = 'bagian_bidang_id'; 
    
    public function index()
    {
        $now = Carbon::now();
        $userKepala = auth()->user();

        // ==========================================
        // ALGORITMA HIERARKI STRUKTUR ORGANISASI
        // ==========================================
        $bawahanIds = [];

        if ($userKepala->hasRole('Kepala Kantor')) {
            // Kepala Kantor: Melihat SEMUA pegawai
            $bawahanIds = User::role('pegawai')->pluck('id')->toArray();
        } 
        elseif ($userKepala->hasAnyRole(['Kepala Bagian', 'Kepala Bidang', 'Kepala TU'])) {
            // Kepala Bidang/Bagian: Melihat pegawai yang bagian_bidang_id-nya SAMA
            $bawahanIds = User::role('pegawai')
                ->where('bagian_bidang_id', $userKepala->bagian_bidang_id)
                ->pluck('id')->toArray();
        } 
        elseif ($userKepala->hasAnyRole(['Kepala Seksi', 'Kepala Sub-Bagian'])) {
            // Kepala Seksi/Sub: Melihat pegawai yang sub_bagian_seksi_id-nya SAMA
            $bawahanIds = User::role('pegawai')
                ->where('sub_bagian_seksi_id', $userKepala->sub_bagian_seksi_id)
                ->pluck('id')->toArray();
        }
        
        // Jaga-jaga jika array kosong
        if(empty($bawahanIds)) { $bawahanIds = [0]; }

        // ==========================================
        // 1. STATISTIK UTAMA (Difilter berdasar hierarki)
        // ==========================================
        $countMenunggu = PengajuanCuti::where($this->kolomStatus, 'menunggu')
            ->whereIn('user_id', $bawahanIds)->count();
        
        $countDisetujui = PengajuanCuti::where($this->kolomStatus, 'disetujui')
            ->whereMonth('updated_at', $now->month)
            ->whereYear('updated_at', $now->year)
            ->whereIn('user_id', $bawahanIds)->count();
            
        $countDitolak = PengajuanCuti::whereIn($this->kolomStatus, ['ditolak', 'revisi'])
            ->whereIn('user_id', $bawahanIds)->count();
        
        // TOTAL PEGAWAI: Tetap Hitung Semua (Request Khusus)
        $totalPegawai = User::role('pegawai')->count();

        // ==========================================
        // 2. DATA DRILL-DOWN 
        // ==========================================
        $menungguPerDivisi = PengajuanCuti::select('bagian_bidangs.'.$this->kolomNamaDivisi, DB::raw('count(*) as total'))
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('bagian_bidangs', 'users.'.$this->fkDivisi, '=', 'bagian_bidangs.id')
            ->where('pengajuan_cutis.'.$this->kolomStatus, 'menunggu')
            ->whereIn('pengajuan_cutis.user_id', $bawahanIds) // FILTER BAWAHAN
            ->groupBy('bagian_bidangs.'.$this->kolomNamaDivisi)
            ->pluck('total', $this->kolomNamaDivisi);

        // ==========================================
        // 3. TREN BULANAN
        // ==========================================
        $trenBulananData = PengajuanCuti::selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
            ->whereYear('tanggal_mulai', $now->year)
            ->where($this->kolomStatus, 'disetujui')
            ->whereIn('user_id', $bawahanIds) // FILTER BAWAHAN
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $trenBulanan = [];
        for ($i = 1; $i <= 12; $i++) { $trenBulanan[] = $trenBulananData[$i] ?? 0; }

        // ==========================================
        // 4. RISIKO KEKOSONGAN (Hanya Bidang yang ada bawahannya)
        // ==========================================
        $divisiData = BagianBidang::with(['users' => function($query) use ($bawahanIds) {
            $query->role('pegawai')->whereIn('id', $bawahanIds);
        }])->get();

        $risikoDivisi = [];
        foreach ($divisiData as $divisi) {
            $totalPegawaiDivisi = $divisi->users->count();
            if ($totalPegawaiDivisi > 0) {
                $pegawaiCutiSaatIni = PengajuanCuti::whereIn('user_id', $divisi->users->pluck('id'))
                    ->where($this->kolomStatus, 'disetujui')
                    ->where('tanggal_mulai', '<=', $now->toDateString())
                    ->where('tanggal_selesai', '>=', $now->toDateString())
                    ->count();

                $persentase = ($pegawaiCutiSaatIni / $totalPegawaiDivisi) * 100;
                $risikoDivisi[] = [
                    'nama_divisi' => $divisi->{$this->kolomNamaDivisi},
                    'total_pegawai' => $totalPegawaiDivisi,
                    'sedang_cuti' => $pegawaiCutiSaatIni,
                    'persentase' => round($persentase, 1),
                    'status_bahaya' => $persentase > 20
                ];
            }
        }

        // ==========================================
        // 5. TABEL PERSETUJUAN TERKINI
        // ==========================================
        $pengajuanTerbaru = PengajuanCuti::with(['user.bagianBidang', 'jenisCuti'])
            ->where($this->kolomStatus, 'menunggu')
            ->whereIn('user_id', $bawahanIds) // FILTER BAWAHAN
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard-kepala', compact(
            'countMenunggu', 'countDisetujui', 'countDitolak', 'totalPegawai',
            'menungguPerDivisi', 'trenBulanan', 'risikoDivisi', 'pengajuanTerbaru'
        ));
    }
}