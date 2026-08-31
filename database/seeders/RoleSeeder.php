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
        // 1. Susunan Role
        $roles = [
            'Pegawai',
            'Admin Kepegawaian',
            'Kepala Seksi',
            'Kepala Sub-Bagian',
            'Kepala Bagian',
            'Kepala Kantor',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ==========================================
        // PEMBUATAN AKUN DARI JABATAN TERTINGGI
        // (Agar ID atasannya bisa ditarik oleh bawahan)
        // ==========================================

        // 2. Akun Kepala Kantor (Tertinggi, tidak punya atasan)
        $kepalaKantor = User::updateOrCreate(
            ['email' => 'kakan@cuti.com'],
            [
                'name' => 'Bapak Kepala Kantor',
                'password' => Hash::make('kepala123'),
                'nip' => '197001012000011001',
                'jabatan' => 'Kepala Kantor Otoritas',
                'divisi' => 'Pimpinan',
                'sisa_cuti_tahunan' => 12,
                'atasan_id' => null, // Tidak ada atasan
            ]
        );
        $kepalaKantor->assignRole('Kepala Kantor');

        // 3. Akun Kepala Bagian (Atasannya Kepala Kantor)
        $kepalaBagian = User::updateOrCreate(
            ['email' => 'kabag@cuti.com'],
            [
                'name' => 'R. Muhammad Wafi Cahyono',
                'password' => Hash::make('kepala123'),
                'nip' => '198002022005011002',
                'jabatan' => 'Kepala Bagian Umum',
                'divisi' => 'Umum',
                'sisa_cuti_tahunan' => 12,
                'atasan_id' => $kepalaKantor->id, // Lapor ke Kakan
            ]
        );
        $kepalaBagian->assignRole('Kepala Bagian');

        // 4. Akun Kepala Seksi (Atasannya Kepala Bagian)
        $kepalaSeksi = User::updateOrCreate(
            ['email' => 'kasi@cuti.com'],
            [
                'name' => 'Kemas Fatih Amanaser Razan',
                'password' => Hash::make('kepala123'),
                'nip' => '198503032010011003',
                'jabatan' => 'Kepala Seksi Jaringan',
                'divisi' => 'Teknologi Informasi',
                'sisa_cuti_tahunan' => 12,
                'atasan_id' => $kepalaBagian->id, // Lapor ke Kabag
            ]
        );
        $kepalaSeksi->assignRole('Kepala Seksi');

        // 5. Akun Admin Kepegawaian (Atasannya Kepala Bagian)
        $admin = User::updateOrCreate(
            ['email' => 'admin@cuti.com'],
            [
                'name' => 'Yuni Admin',
                'password' => Hash::make('admin123'),
                'nip' => '199004042015012004',
                'jabatan' => 'Staff Kepegawaian',
                'divisi' => 'Kepegawaian',
                'sisa_cuti_tahunan' => 12,
                'atasan_id' => $kepalaBagian->id, // Lapor ke Kabag
            ]
        );
        $admin->assignRole('Admin Kepegawaian');

        // 6. Akun Pegawai (Atasannya Kepala Seksi)
        $pegawai = User::updateOrCreate(
            ['email' => 'pegawai@cuti.com'],
            [
                'name' => 'Muhammad Farros Nidji',
                'password' => Hash::make('pegawai123'),
                'nip' => '199505052020011005',
                'jabatan' => 'Staff IT',
                'divisi' => 'Teknologi Informasi',
                'sisa_cuti_tahunan' => 12,
                'atasan_id' => $kepalaSeksi->id, // Lapor ke Kasi
            ]
        );
        $pegawai->assignRole('Pegawai');
    }
}