<?php

namespace App\Imports;

use App\Models\PengajuanCuti;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class JatahCutiSheetImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private const TAHUN_SHEET = 2026;

    public function model(array $row)
    {
        if (empty($row['nip'])) {
            return null;
        }

        $user = User::where('nip', str_replace(' ', '', $row['nip']))->first();
        if (!$user) {
            return null;
        }

        $hitung25  = (int) round((float) ($row['hitung_sisa_tahun_2025'] ?? 0));
        $sisaLalu  = max(0, $hitung25);
        $utangLalu = max(0, -$hitung25);

        $jatahIni = (int) round((float) ($row['asli_sisa_tahun_2026'] ?? 12));
        $ctSheet  = (int) round((float) ($row['total_ct_tahun_2026'] ?? 0));

        $tercatat = (int) PengajuanCuti::where('user_id', $user->id)
            ->where('approval_step', 8)
            ->whereYear('tanggal_mulai', self::TAHUN_SHEET)
            ->whereHas('jenisCuti', fn ($q) => $q->where('mengurangi_kuota', true))
            ->sum('durasi_hari');

        $user->saldo_tahun_lalu = $sisaLalu;
        $user->jatah_cuti       = $sisaLalu + $jatahIni;
        $user->koreksi_terpakai = ($ctSheet - $tercatat) + $utangLalu;
        $user->save();

        return null;
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}