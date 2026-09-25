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

        $nipBersih = str_replace(' ', '', $row['nip']);
        $user = User::where('nip', $nipBersih)->first();

        if (!$user) {
            return null; // NIP tidak ketemu di sistem — lewati, jangan bikin akun baru dari sheet ini
        }

        $finalSisa2025 = (float) ($row['final_sisa_tahun_2025'] ?? 0);
        $asliSisa2026  = (float) ($row['asli_sisa_tahun_2026'] ?? 12);

        // Jatah "kotor" tahun ini: sisa tahun lalu (sudah dibatasi maks 6 hari
        // oleh HR) + jatah segar tahun berjalan. Pemakaian tahun berjalan
        // TIDAK dikurangkan di sini — biarkan sistem yang menghitungnya
        // sendiri dari data pengajuan, supaya tidak terpotong dua kali.
        $user->jatah_cuti = (int) round($finalSisa2025 + $asliSisa2026);
        $user->save();

        return null;
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}