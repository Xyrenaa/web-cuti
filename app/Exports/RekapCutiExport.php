<?php

namespace App\Exports;

use App\Models\User;
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
    protected int $no = 0;

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

    // Definisi yang sama dengan halaman Rekap: disetujui + berdasarkan TANGGAL MULAI
    protected function constraintPengajuan()
    {
        return function ($q) {
            $q->where('approval_step', 8)->whereYear('tanggal_mulai', $this->tahun);
            if ($this->bulan) {
                $q->whereMonth('tanggal_mulai', $this->bulan);
            }
            if ($this->jenisCutiId) {
                $q->where('jenis_cuti_id', $this->jenisCutiId);
            }
        };
    }

    public function collection()
    {
        $tahun = $this->tahun;
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
        if ($this->jenisCutiId || $this->bulan) {
            $query->whereHas('pengajuanCutis', $this->constraintPengajuan());
        }

        $query->withSum(['pengajuanCutis as terpakai_tahun' => function ($q) use ($tahun) {
            $q->where('approval_step', 8)->whereYear('tanggal_mulai', $tahun)
              ->whereHas('jenisCuti', fn ($j) => $j->where('mengurangi_kuota', true));
        }], 'durasi_hari');

        $query->withCount(['pengajuanCutis as jumlah_ajuan' => $this->constraintPengajuan()]);

            $query->withExists(['pengajuanCutis as punya_cuti_besar' => function ($q) use ($tahun) {
            $q->where('approval_step', 8)->whereYear('tanggal_mulai', $tahun)
              ->whereHas('jenisCuti', fn ($j) => $j->where('nama_cuti', 'Cuti Besar'));
        }]);

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'NO', 'NAMA PEGAWAI', 'NIP', 'UNIT',
            'SISA THN LALU DIBAWA', 'TERPAKAI DARI THN LALU', 'SISA THN LALU (FINAL)',
            'JATAH THN INI', 'TERPAKAI THN INI', 'SISA THN INI (FINAL)',
            'JUMLAH AJUAN', 'TOTAL TERPAKAI', 'TOTAL SISA',
        ];
    }

    public function map($user): array
    {
        $s = User::saldoDari($user, (int) ($user->terpakai_tahun ?? 0), (bool) $user->punya_cuti_besar);

        return [
            ++$this->no,
            $user->name,
            $user->nip,
            $user->subBagianSeksi->nama ?? $user->bagianBidang->nama ?? '-',
            $s['saldo_lalu'], $s['dipakai_lalu'], $s['sisa_lalu'],
            $s['jatah_berjalan'], $s['dipakai_berjalan'], $s['sisa_berjalan'],
            (int) $user->jumlah_ajuan, $s['terpakai'], $s['total_sisa'],
        ];
    }
}