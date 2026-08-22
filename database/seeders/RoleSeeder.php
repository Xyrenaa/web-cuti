<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Pegawai',
            'Admin Kepegawaian',
            'Kepala Seksi',
            'Kepala Bidang',
            'Kepala Sub Bagian',
            'Kepala TU',
            'Kepala Kantor',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        $pegawai = User::create([
            'name' => 'Muhammad Farros Nidji',
            'email' => 'pegawai@cuti.com',
            'password' => Hash::make ('pegawai123'),
            'jabatan' => 'Staff IT',
            'sisa_cuti_tahunan' => 12
        ]);
        $pegawai->assignRole('Pegawai');

        $admin = User::create ([
            'name' => 'Yuni Admin',
            'email' => 'admin@cuti.com',
            'password' => Hash::make ('admin123'),
            'jabatan' => 'Staff Kepegawaian',
            'sisa_cuti_tahunan' => 12
        ]);
        $admin->assignRole('Admin Kepegawaian');
    }
}
