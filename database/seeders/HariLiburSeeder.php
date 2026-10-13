<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HariLibur;

class HariLiburSeeder extends Seeder
{
    public function run(): void
    {
        $libur = [
            // Tanggal tetap setiap tahun
            ['2026-01-01', 'Tahun Baru Masehi'],
            ['2026-05-01', 'Hari Buruh Internasional'],
            ['2026-06-01', 'Hari Lahir Pancasila'],
            ['2026-08-17', 'Hari Kemerdekaan RI'],
            ['2026-12-25', 'Hari Raya Natal'],
            ['2027-01-01', 'Tahun Baru Masehi'],
            ['2027-05-01', 'Hari Buruh Internasional'],
            ['2027-06-01', 'Hari Lahir Pancasila'],
            ['2027-08-17', 'Hari Kemerdekaan RI'],
            ['2027-12-25', 'Hari Raya Natal'],

            // TODO: tambahkan libur yang tanggalnya berubah tiap tahun (Idul Fitri,
            // Idul Adha, Nyepi, Waisak, Imlek, Isa Almasih, dst) beserta cuti bersama,
            // salin dari SKB 3 Menteri tahun yang bersangkutan.
        ];

        foreach ($libur as [$tanggal, $keterangan]) {
            HariLibur::updateOrCreate(['tanggal' => $tanggal], ['keterangan' => $keterangan]);
        }
    }
}