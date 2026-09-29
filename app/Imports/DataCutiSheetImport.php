<?php

namespace App\Imports;

use App\Models\User;
use App\Models\PengajuanCuti;
use App\Models\JenisCuti;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DataCutiSheetImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private const ALIAS_JENIS = [
        'cuti tahunan'               => 'Cuti Tahunan',
        'cuti sakit'                 => 'Cuti Sakit',
        'cuti karena alasan penting' => 'Cuti Alasan Penting',
        'cuti alasan penting'        => 'Cuti Alasan Penting',
        'cuti besar'                 => 'Cuti Besar',
        'cuti melahirkan'            => 'Cuti Melahirkan',
    ];

    // Nama di Excel yang tidak bisa dicocokkan otomatis => NIP
    private const ALIAS_NAMA_KE_NIP = [
        'MOCHAMMAD MEGA HERDIYANSYA' => '198405222007121003',
    ];

    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    public function model(array $row)
    {
        $nama       = trim((string) ($row['nama'] ?? ''));
        $jenisExcel = trim((string) ($row['jenis'] ?? ''));
        $kodeExcel  = trim((string) ($row['kode'] ?? 'MIGRASI'));

        if ($nama === '' || $jenisExcel === '') return null;

        $user = $this->cariUser($nama);
        if (!$user) {
            Log::warning("[Import cuti] Pegawai '{$nama}' tidak ditemukan/ambigu. Baris {$kodeExcel} dilewati.");
            return null;
        }

        $namaJenis = self::ALIAS_JENIS[mb_strtolower($jenisExcel)] ?? $jenisExcel;
        $jenis     = JenisCuti::where('nama_cuti', $namaJenis)->first();
        if (!$jenis) {
            Log::warning("[Import cuti] Jenis '{$jenisExcel}' tidak dikenal. Baris {$kodeExcel} dilewati.");
            return null;
        }

        $mulai   = $this->parseTanggal($row['mulai'] ?? null);
        $selesai = $this->parseTanggal($row['selesai'] ?? null) ?? $mulai;
        if (!$mulai) {
            Log::warning("[Import cuti] Tanggal mulai tidak terbaca. Baris {$kodeExcel} dilewati.");
            return null;
        }

        // Aman diupload berulang: riwayat yang sama tidak akan dobel.
        $sudahAda = PengajuanCuti::where('user_id', $user->id)
            ->where('jenis_cuti_id', $jenis->id)
            ->where('tanggal_mulai', $mulai)
            ->where('tanggal_selesai', $selesai)
            ->exists();
        if ($sudahAda) return null;

        $isCancel = strtoupper($kodeExcel) === 'CANCEL';
        $kodeFix  = $isCancel ? 'CANCEL-' . Str::upper(Str::random(5)) : $kodeExcel;

        // Tanggal pengajuan asli dari Excel; kalau gagal dibaca, pakai tanggal mulai (bukan now()).
        $tanggalPengajuan = $this->parseWaktu($row['tanggal'] ?? null) ?? ($mulai . ' 00:00:00');

        $pengajuan = new PengajuanCuti([
            'kode_pengajuan'   => $kodeFix,
            'user_id'          => $user->id,
            'jenis_cuti_id'    => $jenis->id,
            'tanggal_mulai'    => $mulai,
            'tanggal_selesai'  => $selesai,
            // LAMA berisi "-" untuk Cuti Besar => 0 (sama seperti perilaku lama)
            'durasi_hari'      => is_numeric($row['lama'] ?? null) ? (int) $row['lama'] : 0,
            'alasan'           => 'Migrasi rekap manual historis',
            'status_pengajuan' => $isCancel ? 'Dibatalkan' : 'Disetujui',
            'approval_step'    => $isCancel ? 10 : 8,
        ]);
        $pengajuan->created_at = $tanggalPengajuan;
        $pengajuan->updated_at = $tanggalPengajuan;

        return $pengajuan;
    }

    private function cariUser(string $nama): ?User
    {
        $kunci = strtoupper($nama);
        if (isset(self::ALIAS_NAMA_KE_NIP[$kunci])) {
            return User::where('nip', self::ALIAS_NAMA_KE_NIP[$kunci])->first();
        }

        // 1) persis sama
        $u = User::whereRaw('LOWER(name) = ?', [mb_strtolower($nama)])->first();
        if ($u) return $u;

        // 2) diawali nama ini lalu koma/spasi (nama tanpa gelar): "LESTARI" -> "LESTARI, ST, M.Sc."
        $k = User::where('name', 'like', $nama . ',%')->orWhere('name', 'like', $nama . ' %')->get();
        if ($k->count() === 1) return $k->first();

        // 3) mengandung nama, hanya kalau tepat 1 orang
        $k = User::where('name', 'like', '%' . $nama . '%')->get();
        return $k->count() === 1 ? $k->first() : null;
    }

    /** Terima: datetime, angka serial Excel, "05 Januari 2026", "24/06/2026", "2026-06-24". */
    private function parseTanggal($v): ?string
    {
        if ($v === null || $v === '') return null;

        if ($v instanceof \DateTimeInterface) return Carbon::instance($v)->format('Y-m-d');
        if (is_numeric($v))                   return Date::excelToDateTimeObject((float) $v)->format('Y-m-d');

        $v = trim((string) $v);

        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', $v, $m)) {
            $bulan = self::BULAN[strtolower($m[2])] ?? null;
            if ($bulan) return sprintf('%04d-%02d-%02d', $m[3], $bulan, $m[1]);
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $v);
                if ($d && $d->format($fmt) === $v) return $d->format('Y-m-d');
            } catch (\Throwable $e) {}
        }
        return null;
    }

    private function parseWaktu($v): ?string
    {
        if ($v === null || $v === '') return null;

        if ($v instanceof \DateTimeInterface) return Carbon::instance($v)->format('Y-m-d H:i:s');
        if (is_numeric($v))                   return Date::excelToDateTimeObject((float) $v)->format('Y-m-d H:i:s');

        $tgl = $this->parseTanggal($v);
        return $tgl ? $tgl . ' 00:00:00' : null;
    }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 100; }
}