<?php

namespace App\Imports;

use App\Models\User;
use App\Models\PengajuanCuti;
use App\Models\JenisCuti;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date; // <-- Ini sudah terpasang dengan benar

class RekapCutiImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        // PENCEGAH ERROR: Abaikan jika baris tersebut kosong
        if (!isset($row['nama']) || empty($row['nama'])) {
            return null;
        }

        $user = User::where('name', 'LIKE', '%' . $row['nama'] . '%')->first();
        
        if (!isset($row['jenis'])) return null; // Skip jika jenis cuti kosong

        $jenisCuti = JenisCuti::where('nama_cuti', 'LIKE', '%' . $row['jenis'] . '%')->first();
        $jenisCutiId = $jenisCuti ? $jenisCuti->id : 1; 

        if ($user) {
            $kodeExcel = $row['kode'] ?? 'MIGRASI';
            $isCancel = (strtoupper($kodeExcel) === 'CANCEL');
            $status = $isCancel ? 'Dibatalkan' : 'Disetujui';
            
            $kodeFix = $isCancel ? 'CANCEL-' . Str::upper(Str::random(5)) : $kodeExcel;

            // ==========================================
            // LOGIKA TIME TRAVEL DIMULAI DI SINI
            // ==========================================
            $tanggalExcel = $row['tanggal'] ?? null;
            $tanggalAsli = now(); // Default ke hari ini jika kosong

            if ($tanggalExcel) {
                try {
                    // Cek apakah formatnya angka (serial number Excel)
                    if (is_numeric($tanggalExcel)) {
                        $tanggalAsli = Date::excelToDateTimeObject($tanggalExcel)->format('Y-m-d H:i:s');
                    } else {
                        // Jika formatnya teks tanggal biasa
                        $tanggalAsli = Carbon::parse($tanggalExcel)->format('Y-m-d H:i:s');
                    }
                } catch (\Exception $e) {
                    $tanggalAsli = now();
                }
            }

            // Tampung data ke variabel dulu, JANGAN langsung di-return
            $pengajuan = new PengajuanCuti([
                'kode_pengajuan'  => $kodeFix,
                'user_id'         => $user->id,
                'jenis_cuti_id'   => $jenisCutiId,
                'tanggal_mulai'   => $this->parseTanggalIndo($row['mulai']),
                'tanggal_selesai' => $this->parseTanggalIndo($row['selesai']),
                'durasi_hari'     => isset($row['lama']) ? (int) $row['lama'] : 1,
                'alasan'          => 'Migrasi rekap manual historis',
                'status_pengajuan'=> $status,
                'approval_step'   => $isCancel ? 10 : 8,
            ]);

            // Paksa sistem menyimpan dengan tanggal dari Excel, bukan tanggal hari ini
            $pengajuan->created_at = $tanggalAsli;
            $pengajuan->updated_at = $tanggalAsli;

            return $pengajuan;
            // ==========================================
            // LOGIKA TIME TRAVEL SELESAI
            // ==========================================
        }

        return null;
    }

    private function parseTanggalIndo($tanggal) {
        if(!$tanggal) return null;
        $bulan = ['Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04', 'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08', 'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'];
        
        $parts = explode(' ', trim($tanggal));
        if(count($parts) == 3) {
            $d = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $m = $bulan[$parts[1]] ?? '01';
            $y = $parts[2];
            return "$y-$m-$d";
        }
        return date('Y-m-d', strtotime($tanggal)); 
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