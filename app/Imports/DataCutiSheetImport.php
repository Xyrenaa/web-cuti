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
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DataCutiSheetImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
       public function model(array $row)
    {
        if (!isset($row['nama']) || empty($row['nama'])) {
            return null;
        }

        $user = User::where('name', 'LIKE', '%' . $row['nama'] . '%')->first();

        if (!isset($row['jenis'])) return null;

        $jenisCuti = JenisCuti::where('nama_cuti', 'LIKE', '%' . $row['jenis'] . '%')->first();
        $jenisCutiId = $jenisCuti ? $jenisCuti->id : 1;

        if (!$user) {
            return null;
        }

        $tanggalMulai = $this->parseTanggalIndo($row['mulai']);
        $tanggalSelesai = $this->parseTanggalIndo($row['selesai']);

        // CEK DUPLIKAT: kalau riwayat cuti orang ini, jenis ini, tanggal ini
        // sudah pernah tercatat sebelumnya, lewati. Ini WAJIB ada supaya file
        // yang sama aman diupload berkali-kali (misalnya nanti mau update
        // sheet JATAH CUTI lagi) tanpa bikin data cuti dobel atau bentrok
        // kode_pengajuan.
        $sudahAda = PengajuanCuti::where('user_id', $user->id)
            ->where('jenis_cuti_id', $jenisCutiId)
            ->where('tanggal_mulai', $tanggalMulai)
            ->where('tanggal_selesai', $tanggalSelesai)
            ->exists();

        if ($sudahAda) {
            return null;
        }

        $kodeExcel = $row['kode'] ?? 'MIGRASI';
        $isCancel = (strtoupper($kodeExcel) === 'CANCEL');
        $status = $isCancel ? 'Dibatalkan' : 'Disetujui';

        $kodeFix = $isCancel ? 'CANCEL-' . Str::upper(Str::random(5)) : $kodeExcel;

        $tanggalExcel = $row['tanggal'] ?? null;
        $tanggalAsli = now();

        if ($tanggalExcel) {
            try {
                if (is_numeric($tanggalExcel)) {
                    $tanggalAsli = Date::excelToDateTimeObject($tanggalExcel)->format('Y-m-d H:i:s');
                } else {
                    $tanggalAsli = Carbon::parse($tanggalExcel)->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                $tanggalAsli = now();
            }
        }

        $pengajuan = new PengajuanCuti([
            'kode_pengajuan'  => $kodeFix,
            'user_id'         => $user->id,
            'jenis_cuti_id'   => $jenisCutiId,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'durasi_hari'     => isset($row['lama']) ? (int) $row['lama'] : 1,
            'alasan'          => 'Migrasi rekap manual historis',
            'status_pengajuan'=> $status,
            'approval_step'   => $isCancel ? 10 : 8,
        ]);

        $pengajuan->created_at = $tanggalAsli;
        $pengajuan->updated_at = $tanggalAsli;

        return $pengajuan;
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

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}