<?php

namespace App\Imports;

use App\Models\User;
use App\Models\SubBagianSeksi;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PegawaiImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        if (!isset($row['nama']) || empty($row['nip'])) {
            return null;
        }

        // ==============================================================
        // SOLUSI DUPLIKAT: Bersihkan semua spasi dari NIP Excel
        // ==============================================================
        $nipBersih = str_replace(' ', '', $row['nip']);

        $subBagianId = null;
        $bagianId = null;
        $namaSubExcel = $row['subbag_seksi'] ?? null; 

        if ($namaSubExcel) {
            $subBagian = SubBagianSeksi::where('nama', 'LIKE', '%' . trim($namaSubExcel) . '%')->first();
            if ($subBagian) {
                $subBagianId = $subBagian->id;
                $bagianId = $subBagian->bagian_bidang_id;
            }
        }

        // Cari berdasarkan NIP yang sudah bersih dari spasi (atau NIP asli berjaga-jaga)
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

            if ($isUpdated) {
                $user->save();
            }
            return null; 
        }

        $emailDummy = strtolower($nipBersih) . '@otban3.com';

        return new User([
            'name'                => $row['nama'],
            'nip'                 => $nipBersih, // Simpan ke database tanpa spasi agar seragam
            'email'               => $emailDummy,
            'password'            => Hash::make('password123'), 
            'jenis_kelamin'       => $row['jenis_kelamin'] ?? null,
            'bagian_bidang_id'    => $bagianId,
            'sub_bagian_seksi_id' => $subBagianId,
            'level_jabatan'       => 'Pegawai',
            'jatah_cuti'          => 12,
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}