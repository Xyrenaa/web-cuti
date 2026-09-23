<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisCuti;

class JenisCutiSeeder extends Seeder
{
    public function run(): void
    {
        $jenis_cuti = [
            ['nama_cuti' => 'Cuti Tahunan', 'mengurangi_kuota' => true, 'wajib_lampiran' => false, 'untuk_pppk' => true, 'khusus_perempuan' => false],
            ['nama_cuti' => 'Cuti Sakit', 'mengurangi_kuota' => false, 'wajib_lampiran' => true, 'untuk_pppk' => true, 'khusus_perempuan' => false],
            ['nama_cuti' => 'Cuti Alasan Penting', 'mengurangi_kuota' => false, 'wajib_lampiran' => true, 'untuk_pppk' => false, 'khusus_perempuan' => false],
            ['nama_cuti' => 'Cuti Besar', 'mengurangi_kuota' => true, 'wajib_lampiran' => false, 'untuk_pppk' => false, 'khusus_perempuan' => false],
            ['nama_cuti' => 'Cuti Melahirkan', 'mengurangi_kuota' => false, 'wajib_lampiran' => true, 'untuk_pppk' => true, 'khusus_perempuan' => true],
            ['nama_cuti' => 'Cuti Bersama', 'mengurangi_kuota' => false, 'wajib_lampiran' => false, 'untuk_pppk' => true, 'khusus_perempuan' => false],
        ];

        foreach ($jenis_cuti as $jenis) {
            JenisCuti::updateOrCreate(['nama_cuti' => $jenis['nama_cuti']], $jenis);
        }
    }
}