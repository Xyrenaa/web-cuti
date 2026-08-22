<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisCuti;

class JenisCutiSeeder extends Seeder
{
    public function run(): void
    {
        $jenis_cuti = [
            ['nama_cuti'=> 'Cuti Tahunan','mengurangi_kuota' => true, 'wajib_lampiran' => false],
            ['nama_cuti'=> 'Cuti Sakit', 'mengurangi_kuota'=> false, 'wajib_lampiran' => true],
            ['nama_cuti'=> 'Cuti Alasan Penting', 'mengurangi_kuota'=> false, 'wajib_lampiran'=> true],
            ['nama_cuti'=> 'Cuti Besar', 'mengurangi_kuota'=> true, 'wajib_lampiran'=> false]
        ];

        foreach($jenis_cuti as $jenis){
            JenisCuti::create($jenis);
        }
    }
}
