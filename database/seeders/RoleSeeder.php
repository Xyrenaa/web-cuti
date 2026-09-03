<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\BagianBidang;
use App\Models\SubBagianSeksi;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT ROLE SPATIE (Sesuai dengan hierarki)
        $roles = [
            'Kepala Kantor',
            'Kepala TU',
            'Kepala Bidang',
            'Kepala Sub-Bagian',
            'Kepala Seksi',
            'Admin Kepegawaian',
            'Pegawai'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. BUAT MASTER DATA BAGIAN/BIDANG & SUB-BAGIAN/SEKSI
        
        // --- Ekosistem Tata Usaha (TU) ---
        $tu = BagianBidang::create(['nama' => 'Bagian Tata Usaha', 'is_tu' => true]);
        $subKeuangan = SubBagianSeksi::create(['bagian_bidang_id' => $tu->id, 'nama' => 'Sub Bagian Perencanaan Dan Keuangan']);
        $subKepegawaian = SubBagianSeksi::create(['bagian_bidang_id' => $tu->id, 'nama' => 'Sub Bagian Umum Dan Kepegawaian']);

        // --- Ekosistem Bidang Pelayanan ---
        $bidangPelayanan = BagianBidang::create(['nama' => 'Bidang Pelayanan Dan Pengoperasian Bandar Udara', 'is_tu' => false]);
        $seksiFasilitas = SubBagianSeksi::create(['bagian_bidang_id' => $bidangPelayanan->id, 'nama' => 'Seksi Fasilitas Dan Pelayanan Bandar Udara']);
        $seksiPengoperasian = SubBagianSeksi::create(['bagian_bidang_id' => $bidangPelayanan->id, 'nama' => 'Seksi Pengoperasian Bandar Udara']);

        // --- Ekosistem Bidang Keamanan ---
        $bidangKeamanan = BagianBidang::create(['nama' => 'Bidang Keamanan, Angkutan Udara Dan Kelaikudaraan', 'is_tu' => false]);
        $seksiKeamanan = SubBagianSeksi::create(['bagian_bidang_id' => $bidangKeamanan->id, 'nama' => 'Seksi Keamanan Penerbangan & Pelayanan Darurat']);
        $seksiAngkutan = SubBagianSeksi::create(['bagian_bidang_id' => $bidangKeamanan->id, 'nama' => 'Seksi Angkutan Udara, Kelaikudaraan & Pengoperasian Pesawat Udara']);


        // 3. BUAT AKUN KEPALA BERDASARKAN STRUKTUR ORGANISASI
        $defaultPassword = Hash::make('kepala123'); // Password default untuk testing

        // --- KEPALA KANTOR ---
        $kakan = User::create([
            'name' => 'AGUSTONO, S.SOS. M.MTR',
            'nip' => '196908311991031001', // Spasi dihilangkan agar mudah untuk login
            'email' => 'kakan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Kantor',
            'bagian_bidang_id' => null,
            'sub_bagian_seksi_id' => null,
            'jatah_cuti' => 12,
        ]);
        $kakan->assignRole('Kepala Kantor');

        // --- KEPALA BAGIAN TATA USAHA ---
        $kabagTu = User::create([
            'name' => 'DIAN WAHYUDI. M. SI',
            'nip' => '198002202000121003',
            'email' => 'kabag.tu@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => null,
        ]);
        $kabagTu->assignRole('Kepala TU');

        // --- KEPALA SUB BAGIAN (TU) ---
        $kasubKeuangan = User::create([
            'name' => 'MASRUKHIN. A.MD',
            'nip' => '197710151999031002',
            'email' => 'kasub.keuangan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => $subKeuangan->id,
        ]);
        $kasubKeuangan->assignRole('Kepala Sub-Bagian');

        $kasubKepegawaian = User::create([
            'name' => 'DIAH YUNIATI, S.KOM, M.SC',
            'nip' => '198306132006042001',
            'email' => 'kasub.kepegawaian@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => $subKepegawaian->id,
        ]);
        $kasubKepegawaian->assignRole('Kepala Sub-Bagian');

        // --- KEPALA BIDANG ---
        $kabidPelayanan = User::create([
            'name' => 'ERWIN DWI PURNOMO, S.T., M.SC',
            'nip' => '198007302006041001',
            'email' => 'kabid.pelayanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => null,
        ]);
        $kabidPelayanan->assignRole('Kepala Bidang');

        $kabidKeamanan = User::create([
            'name' => 'FUADANI, S.T., M.M',
            'nip' => '197011151993031001',
            'email' => 'kabid.keamanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => null,
        ]);
        $kabidKeamanan->assignRole('Kepala Bidang');

        // --- KEPALA SEKSI ---
        $kasiFasilitas = User::create([
            'name' => 'M. MEGA HERDIYANSYA S.SIT',
            'nip' => '198405222007121003',
            'email' => 'kasi.fasilitas@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => $seksiFasilitas->id,
        ]);
        $kasiFasilitas->assignRole('Kepala Seksi');

        $kasiPengoperasian = User::create([
            'name' => 'CANDRA JAYA, SSIT, MM',
            'nip' => '197912052002121001',
            'email' => 'kasi.pengoperasian@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => $seksiPengoperasian->id,
        ]);
        $kasiPengoperasian->assignRole('Kepala Seksi');

        $kasiKeamanan = User::create([
            'name' => 'ANDY HENDRA SURYAKA, ST., MM',
            'nip' => '197910202002121002',
            'email' => 'kasi.keamanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => $seksiKeamanan->id,
        ]);
        $kasiKeamanan->assignRole('Kepala Seksi');

        $kasiAngkutan = User::create([
            'name' => 'TRI RENGGO JOKO WAHONO, SE',
            'nip' => '197111031990091001',
            'email' => 'kasi.angkutan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => $seksiAngkutan->id,
        ]);
        $kasiAngkutan->assignRole('Kepala Seksi');

        // 8. ADMIN KEPEGAWAIAN (Dimasukkan ke ekosistem Tata Usaha)
        $admin = User::updateOrCreate(
            ['email' => 'admin@cuti.com'],
            [
                'name' => 'Yuni Admin',
                'nip' => '199004042015012004',
                'password' => Hash::make('admin123'),
                'level_jabatan' => 'Pegawai',
                'bagian_bidang_id' => $tu->id,
                'sub_bagian_seksi_id' => $subKepegawaian->id,
                'jatah_cuti' => 12,
            ]
        );
        $admin->assignRole('Admin Kepegawaian');

        // 9. PEGAWAI BIASA (Dimasukkan ke Bidang Pelayanan / Seksi Fasilitas)
        $pegawai = User::updateOrCreate(
            ['email' => 'pegawai@cuti.com'],
            [
                'name' => 'Muhammad Farros Nidji',
                'nip' => '199505052020011005',
                'password' => Hash::make('pegawai123'),
                'level_jabatan' => 'Pegawai',
                'bagian_bidang_id' => $bidangPelayanan->id,
                'sub_bagian_seksi_id' => $seksiFasilitas->id,
                'jatah_cuti' => 12,
            ]
        );
        $pegawai->assignRole('Pegawai');
    }
}