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
    // Skema approval_step kanonik yang dipakai di seluruh aplikasi:
    // 0 = Ditolak, 1-7 = masih berjalan, 8 = Disetujui, 9 = Perlu Revisi, 10 = Dibatalkan
    private $stepMenunggu = [1, 2, 3, 4, 5, 6, 7];
    private $stepDisetujui = 8;
    private $stepDitolakRevisi = [0, 9];

    private $kolomNamaDivisi = 'nama';
    private $fkDivisi = 'bagian_bidang_id';

    public function index()
    {
        $now = Carbon::now();
        $userKepala = auth()->user();

        // ==========================================
        // ALGORITMA HIERARKI STRUKTUR ORGANISASI (tidak diubah)
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

        $bawahanIds = array_filter($bawahanIds, fn($id) => $id !== $userKepala->id);

        if (empty($bawahanIds)) { $bawahanIds = [0]; }

        // Step yang BISA DIPROSES (bukan cuma dilihat) oleh role kepala yang sedang login.
        // Dipakai supaya tabel "Persetujuan Terkini Menunggu Tindakan" hanya nampilin
        // pengajuan yang memang lagi menunggu ACTION dari viewer ini, bukan yang masih
        // berjalan di meja orang lain (mis. Kepala TU, Admin, dst).
        $stepMilikSaya = match (true) {
            $userKepala->hasRole('Kepala Seksi') => 1,
            $userKepala->hasRole('Kepala Bidang') => 2,
            $userKepala->hasRole('Kepala Sub-Bagian') => 4,
            $userKepala->hasRole('Kepala TU') => 5,
            $userKepala->hasRole('Kepala Kantor') => 6,
            default => null,
        };

        // Level hierarki viewer, dipakai buat nentuin cakupan & bentuk chart Risiko Kekosongan:
        // - kantor: lihat semua Bagian/Bidang, tiap slice bisa di-klik buat rincian per Sub-Bagian/Seksi
        // - bidang: hanya Sub-Bagian/Seksi di bawah bagian_bidang miliknya sendiri
        // - seksi : cuma satu angka ringkasan buat seksi/sub-bagiannya sendiri (tidak ada breakdown lagi)
        $levelKepala = match (true) {
            $userKepala->hasRole('Kepala Kantor') => 'kantor',
            $userKepala->hasAnyRole(['Kepala Bagian', 'Kepala Bidang', 'Kepala TU']) => 'bidang',
            $userKepala->hasAnyRole(['Kepala Seksi', 'Kepala Sub-Bagian']) => 'seksi',
            default => null,
        };

        // ==========================================
        // 1. STATISTIK UTAMA (Difilter berdasar hierarki)
        // ==========================================
        // "Pengajuan Menunggu": HANYA yang sedang di step milik viewer ini sendiri
        // (bukan "masih berjalan di manapun" lagi) — begitu lewat dari step-nya,
        // otomatis hilang dari sini dan baru terhitung di dashboard atasan berikutnya.
        $countMenunggu = $stepMilikSaya !== null
            ? PengajuanCuti::where('approval_step', $stepMilikSaya)->count()
            : 0;

        $countDisetujui = PengajuanCuti::where('approval_step', $this->stepDisetujui)
            ->whereMonth('updated_at', $now->month)
            ->whereYear('updated_at', $now->year)
            ->whereIn('user_id', $bawahanIds)->count();

        $countDitolak = PengajuanCuti::whereIn('approval_step', $this->stepDitolakRevisi)
            ->whereIn('user_id', $bawahanIds)->count();

        // TOTAL PEGAWAI: ikut hierarki, sama seperti kartu-kartu lainnya di atas
        // (sebelumnya di-hardcode ke seluruh kantor via User::role('pegawai')->count(),
        // makanya kartu ini selalu 173 berapa pun jabatan yang login).
        // TOTAL PEGAWAI: sekarang dibatasi sesuai cakupan hierarki masing-masing kepala
        // (pakai $bawahanIds yang sudah dihitung sesuai level di atas), bukan seluruh perusahaan lagi.
        $totalPegawai = ($bawahanIds === [0]) ? 0 : count($bawahanIds);

        // ==========================================
        // 2. DATA DRILL-DOWN (ikut disamakan: cuma step milik viewer sendiri)
        // ==========================================
        $menungguPerDivisi = PengajuanCuti::select('bagian_bidangs.'.$this->kolomNamaDivisi, DB::raw('count(*) as total'))
            ->join('users', 'pengajuan_cutis.user_id', '=', 'users.id')
            ->join('bagian_bidangs', 'users.'.$this->fkDivisi, '=', 'bagian_bidangs.id')
            ->when(
                $stepMilikSaya !== null,
                fn ($q) => $q->where('pengajuan_cutis.approval_step', $stepMilikSaya),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->groupBy('bagian_bidangs.'.$this->kolomNamaDivisi)
            ->pluck('total', $this->kolomNamaDivisi);

        // ==========================================
        // 3. TREN BULANAN
        // ==========================================
        $trenBulananData = PengajuanCuti::selectRaw('MONTH(tanggal_mulai) as bulan, COUNT(*) as total')
            ->whereYear('tanggal_mulai', $now->year)
            ->where('approval_step', $this->stepDisetujui)
            ->whereIn('user_id', $bawahanIds) // FILTER BAWAHAN
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $trenBulanan = [];
        for ($i = 1; $i <= 12; $i++) { $trenBulanan[] = $trenBulananData[$i] ?? 0; }

        // ==========================================
        // 4. RISIKO KEKOSONGAN — bentuk & cakupan data beda per level jabatan
        // ==========================================
        $hitungSedangCuti = function (array $userIds) use ($now) {
            if (empty($userIds)) return 0;
            return PengajuanCuti::whereIn('user_id', $userIds)
                ->where('approval_step', $this->stepDisetujui)
                ->where('tanggal_mulai', '<=', $now->toDateString())
                ->where('tanggal_selesai', '>=', $now->toDateString())
                ->count();
        };

        // Sama seperti $hitungSedangCuti, tapi mengembalikan daftar nama pegawainya
        // (dipakai untuk modal rincian di level Bidang & Seksi, yang sudah di titik terkecil).
        $daftarSedangCuti = function (array $userIds) use ($now) {
            if (empty($userIds)) return [];
            return PengajuanCuti::whereIn('user_id', $userIds)
                ->where('approval_step', $this->stepDisetujui)
                ->where('tanggal_mulai', '<=', $now->toDateString())
                ->where('tanggal_selesai', '>=', $now->toDateString())
                ->with('user')
                ->get()
                ->map(fn ($p) => [
                    'nama' => $p->user->name ?? '-',
                    'nip' => $p->user->nip ?? '-',
                    'tanggal_mulai' => \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y'),
                    'tanggal_selesai' => \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y'),
                ])
                ->values()
                ->toArray();
        };

        $risikoDivisi = [];
        $risikoRingkas = null;

        if ($levelKepala === 'kantor') {
            // Kepala Kantor: semua Bagian/Bidang, tiap Bagian/Bidang dibekali rincian
            // per Sub-Bagian/Seksi di dalamnya buat drill-down pas slice-nya diklik.
            $semuaBagian = BagianBidang::with([
                'users' => fn ($q) => $q->role('pegawai'),
                'subBagianSeksis.users' => fn ($q) => $q->role('pegawai'),
            ])->get();

            foreach ($semuaBagian as $bagian) {
                $totalBagian = $bagian->users->count();
                if ($totalBagian === 0) continue;

                $cutiBagian = $hitungSedangCuti($bagian->users->pluck('id')->toArray());
                $persentaseBagian = round(($cutiBagian / $totalBagian) * 100, 1);

                $rincianSub = [];
                foreach ($bagian->subBagianSeksis as $sub) {
                    $totalSub = $sub->users->count();
                    if ($totalSub === 0) continue;
                    $cutiSub = $hitungSedangCuti($sub->users->pluck('id')->toArray());
                    $persentaseSub = round(($cutiSub / $totalSub) * 100, 1);
                    $rincianSub[] = [
                        'nama' => $sub->nama,
                        'total_pegawai' => $totalSub,
                        'sedang_cuti' => $cutiSub,
                        'persentase' => $persentaseSub,
                        'status_bahaya' => $persentaseSub > 20,
                    ];
                }

                $risikoDivisi[] = [
                    'nama_divisi' => $bagian->{$this->kolomNamaDivisi},
                    'total_pegawai' => $totalBagian,
                    'sedang_cuti' => $cutiBagian,
                    'persentase' => $persentaseBagian,
                    'status_bahaya' => $persentaseBagian > 20,
                    'rincian' => $rincianSub,
                ];
            }
        } elseif ($levelKepala === 'bidang') {
            // Kepala Bidang/Bagian/TU: HANYA Sub-Bagian/Seksi di bawah bagian_bidang miliknya sendiri.
            // Sudah di level terkecil, jadi "rincian" berisi NAMA PEGAWAI yang sedang cuti (bukan sub-unit lagi).
            $subs = SubBagianSeksi::where('bagian_bidang_id', $userKepala->bagian_bidang_id)
                ->with(['users' => fn ($q) => $q->role('pegawai')])
                ->get();

            foreach ($subs as $sub) {
                $totalSub = $sub->users->count();
                if ($totalSub === 0) continue;
                $idsSub = $sub->users->pluck('id')->toArray();
                $cutiSub = $hitungSedangCuti($idsSub);
                $persentaseSub = round(($cutiSub / $totalSub) * 100, 1);

                $risikoDivisi[] = [
                    'nama_divisi' => $sub->nama,
                    'total_pegawai' => $totalSub,
                    'sedang_cuti' => $cutiSub,
                    'persentase' => $persentaseSub,
                    'status_bahaya' => $persentaseSub > 20,
                    'rincian' => $daftarSedangCuti($idsSub),
                ];
            }
        } elseif ($levelKepala === 'seksi') {
            // Kepala Seksi/Sub-Bagian: satu angka ringkasan + daftar nama pegawai yang sedang cuti.
            $idsValid = ($bawahanIds === [0]) ? [] : $bawahanIds;
            $totalSeksi = count($idsValid);
            $cutiSeksi = $hitungSedangCuti($idsValid);
            $persentaseSeksi = $totalSeksi > 0 ? round(($cutiSeksi / $totalSeksi) * 100, 1) : 0;

            $risikoRingkas = [
                'total_pegawai' => $totalSeksi,
                'sedang_cuti' => $cutiSeksi,
                'persentase' => $persentaseSeksi,
                'status_bahaya' => $persentaseSeksi > 20,
                'daftar_pegawai' => $daftarSedangCuti($idsValid),
            ];
        }

        // ==========================================
        // 5. TABEL PERSETUJUAN TERKINI (khusus yang menunggu TINDAKAN viewer ini,
        //    TIDAK dibatasi per bagian/bidang lagi — sinkron dengan halaman Approval Cuti)
        // ==========================================
        $pengajuanTerbaru = PengajuanCuti::with(['user.bagianBidang', 'jenisCuti'])
            ->when(
                $stepMilikSaya !== null,
                fn ($q) => $q->where('approval_step', $stepMilikSaya),
                fn ($q) => $q->whereRaw('1 = 0') // role tak dikenal -> jangan tampilkan apapun
            )
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard-kepala', compact(
            'countMenunggu', 'countDisetujui', 'countDitolak', 'totalPegawai',
            'menungguPerDivisi', 'trenBulanan', 'risikoDivisi', 'risikoRingkas', 'levelKepala', 'pengajuanTerbaru'
        ));
    }
}