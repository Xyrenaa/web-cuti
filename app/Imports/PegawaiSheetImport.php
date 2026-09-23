<?php

namespace App\Imports;

use App\Models\User;
use App\Models\SubBagianSeksi;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PegawaiSheetImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // Header "NAMA" (sheet DATA PNS) jadi kunci 'nama'; header "N A M A"
        // (sheet DATA PPPK, ada spasi antar huruf) jadi kunci 'n_a_m_a'.
        $namaLengkap = $row['nama'] ?? $row['n_a_m_a'] ?? null;

        if (!$namaLengkap || empty($row['nip'])) {
            return null;
        }

        $nipBersih = str_replace(' ', '', $row['nip']);
        $status = User::statusKepegawaianDariNip($nipBersih);

        $subBagianId = null;
        $bagianId = null;
        $namaSubExcel = $row['subbag_seksi'] ?? null;

        if ($namaSubExcel) {
            $namaSubExcel = trim($namaSubExcel);
            $subBagian = SubBagianSeksi::where('nama', 'LIKE', '%' . $namaSubExcel . '%')->first();

            if (!$subBagian) {
                $altNama = str_contains($namaSubExcel, ' dan ')
                    ? str_replace(' dan ', ' & ', $namaSubExcel)
                    : str_replace(' & ', ' dan ', $namaSubExcel);
                $subBagian = SubBagianSeksi::where('nama', 'LIKE', '%' . $altNama . '%')->first();
            }

            if ($subBagian) {
                $subBagianId = $subBagian->id;
                $bagianId = $subBagian->bagian_bidang_id;
            }
        }

        $user = User::where('nip', $nipBersih)->orWhere('nip', $row['nip'])->first();

        if ($user) {
            $isUpdated = false;

            if (isset($row['jenis_kelamin']) && !$user->jenis_kelamin) {
                $user->jenis_kelamin = $row['jenis_kelamin'];
                $isUpdated = true;
            }

            if ($subBagianId && !$user->sub_bagian_seksi_id) {
                $user->sub_bagian_seksi_id = $subBagianId;
                $user->bagian_bidang_id = $bagianId;
                $isUpdated = true;
            }

            if ($status && !$user->status_kepegawaian) {
                $user->status_kepegawaian = $status;
                $isUpdated = true;
            }

            if ($isUpdated) {
                $user->save();
            }
            return null;
        }

        $emailDummy = strtolower($nipBersih) . '@otban3.com';

        return new User([
            'name'                => $namaLengkap,
            'nip'                 => $nipBersih,
            'email'               => $emailDummy,
            'password'            => Hash::make('password123'),
            'jenis_kelamin'       => $row['jenis_kelamin'] ?? null,
            'bagian_bidang_id'    => $bagianId,
            'sub_bagian_seksi_id' => $subBagianId,
            'level_jabatan'       => 'Pegawai',
            'jatah_cuti'          => 12,
            'status_kepegawaian'  => $status,
        ]);
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}