<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Idempotent: aman dijalankan berkali-kali & aman di database yang sudah berisi data.
 *   php artisan db:seed --class=SuperadminSeeder
 */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Superadmin']);

        // Password hanya di-set saat akun BARU dibuat; menjalankan ulang seeder
        // tidak akan me-reset password yang sudah diganti.
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@cuti.com'],
            [
                'name'          => 'Super Admin',
                'password'      => Hash::make('superadmin123'), // GANTI setelah login pertama
                'level_jabatan' => 'Pegawai',
                'jatah_cuti'    => 0,
            ]
        );

        if (! $superadmin->hasRole('Superadmin')) {
            $superadmin->assignRole('Superadmin');
        }
    }
}