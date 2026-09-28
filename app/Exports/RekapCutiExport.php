<?php

namespace App\Exports;

use App\Models\User;
use App\Models\PengajuanCuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapCutiExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?string $search;
    protected ?int $divisiId;
    protected ?int $subBagianId;
    protected ?int $jenisCutiId;
    protected int $tahun;
    protected ?int $bulan;

    public function __construct(
        ?string $search = null,
        ?int $divisiId = null,
        ?int $subBagianId = null,
        ?int $jenisCutiId = null,
        ?int $tahun = null,
        ?int $bulan = null
    ) {
        $this->search = $search;
        $this->divisiId = $divisiId;
        $this->subBagianId = $subBagianId;
        $this->jenisCutiId = $jenisCutiId;
        $this->tahun = $tahun ?? (int) date('Y');
        $this->bulan = $bulan;
    }

    /**
     * Constraint pengajuan yang sama persis dengan yang dipakai halaman Rekap,
     * supaya angka di Excel selalu konsisten dengan yang tampil di layar.
     */
    protected function constraintPengajuan()
    {
        return function ($q) {
            $q->where('approval_step', 8)->whereYear('created_at', $this->tahun);
            if ($this->bulan) {
                $q->whereMonth('created_at', $this->bulan);
            }
            if ($this->jenisCutiId) {
                $q->where('jenis_cuti_id', $this->jenisCutiId);
            }
        };
    }

    public function collection()
    {
        $query = User::with(['bagianBidang', 'subBagianSeksi']);

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($this->divisiId) {
            $query->where('bagian_bidang_id', $this->divisiId);
        }

        if ($this->subBagianId) {
            $query->where('sub_bagian_seksi_id', $this->subBagianId);
        }

        if ($this->jenisCutiId) {
            $query->whereHas('pengajuanCutis', $this->constraintPengajuan());
        }

        return $query->orderBy('name')->get();
    }

    // Fungsi ini untuk membuat Judul Kolom (Header) di Excel
    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEGAWAI',
            'NIP',
            'DIVISI / SUBBAGIAN',
            'KUOTA TAHUNAN',
            'JUMLAH AJUAN',
            'CUTI TERPAKAI',
            'SISA KUOTA',
        ];
    }

    // Fungsi ini untuk mengisi baris data ke dalam Excel
    public function map($user): array
    {
        // Bikin nomor urut otomatis
        static $no = 0;
        $no++;

        $constraint = $this->constraintPengajuan();

        $jumlahAjuan = PengajuanCuti::where('user_id', $user->id)->where($constraint)->count();

        $terpakai = PengajuanCuti::where('user_id', $user->id)
            ->where($constraint)
            ->whereHas('jenisCuti', function ($query) {
                $query->where('mengurangi_kuota', true);
            })
            ->sum('durasi_hari');

        $kuota = $user->jatah_cuti ?? 12;
        $divisi = $user->subBagianSeksi->nama ?? $user->bagianBidang->nama ?? '-';

        return [
            $no,
            $user->name,
            $user->nip,
            $divisi,
            $kuota . ' Hari',
            $jumlahAjuan,
            $terpakai . ' Hari',
            ($kuota - $terpakai) . ' Hari',
        ];
    }
}