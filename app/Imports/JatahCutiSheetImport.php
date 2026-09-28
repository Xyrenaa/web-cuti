<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class JatahCutiSheetImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        if (empty($row['nip'])) {
            return null;
        }

        $user = User::where('nip', str_replace(' ', '', $row['nip']))->first();
        if (!$user) {
            return null;
        }

        $asliSisaLalu = (float) ($row['asli_sisa_tahun_2025'] ?? 0);
        $asliSisaIni  = (float) ($row['asli_sisa_tahun_2026'] ?? 12);

        // Sama dengan kolom "Hitung Sisa 2025" di Excel: dibawa maks. 6 hari, tidak boleh negatif.
        $saldoLalu = (int) min(6, max(0, round($asliSisaLalu)));

        $user->saldo_tahun_lalu = $saldoLalu;
        $user->jatah_cuti       = $saldoLalu + (int) round($asliSisaIni); // pemakaian dihitung sistem
        $user->save();

        return null;
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}