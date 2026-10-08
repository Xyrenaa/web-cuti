<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\BagianBidang;
use App\Models\SubBagianSeksi;

class RoleSeeder extends Seeder
{
    /**
     * Seeder ini AMAN dijalankan berulang kali (mis. `php artisan db:seed`
     * hanya untuk menambah akun baru): data yang sudah ada tidak dibuat ulang,
     * dan seluruh proses dibungkus transaksi sehingga kalau ada yang gagal,
     * tidak ada data setengah jadi yang tertinggal.
     */
    public function run(): void
    {
        DB::transaction(fn () => $this->seed());
    }

    /**
     * Buat akun kepala hanya jika belum ada (dicari lewat NIP ATAU email).
     * Akun yang sudah ada tidak disentuh, jadi password/email yang sudah
     * diganti pegawai lewat pop-up login pertama tidak ikut ter-reset.
     * Akun baru otomatis wajib mengganti kredensial awal saat login pertama.
     */
    private function akunKepala(array $data, string $role): User
    {
        $user = User::where('nip', $data['nip'])
            ->orWhere('email', $data['email'])
            ->first();

        if (! $user) {
            $user = User::create($data + ['wajib_ganti_kredensial' => true]);
        }

        $user->assignRole($role); // idempotent: tidak membuat role ganda

        return $user;
    }

    private function seed(): void
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
        $tu = BagianBidang::firstOrCreate(['nama' => 'Bagian Tata Usaha'], ['is_tu' => true]);
        $subKeuangan = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $tu->id, 'nama' => 'Sub Bagian Perencanaan Dan Keuangan']);
        $subKepegawaian = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $tu->id, 'nama' => 'Sub Bagian Umum Dan Kepegawaian']);

        // --- Ekosistem Bidang Pelayanan ---
        $bidangPelayanan = BagianBidang::firstOrCreate(['nama' => 'Bidang Pelayanan Dan Pengoperasian Bandar Udara'], ['is_tu' => false]);
        $seksiFasilitas = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $bidangPelayanan->id, 'nama' => 'Seksi Fasilitas Dan Pelayanan Bandar Udara']);
        $seksiPengoperasian = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $bidangPelayanan->id, 'nama' => 'Seksi Pengoperasian Bandar Udara']);

        // --- Ekosistem Bidang Keamanan ---
        $bidangKeamanan = BagianBidang::firstOrCreate(['nama' => 'Bidang Keamanan, Angkutan Udara Dan Kelaikudaraan'], ['is_tu' => false]);
        $seksiKeamanan = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $bidangKeamanan->id, 'nama' => 'Seksi Keamanan Penerbangan & Pelayanan Darurat']);
        $seksiAngkutan = SubBagianSeksi::firstOrCreate(['bagian_bidang_id' => $bidangKeamanan->id, 'nama' => 'Seksi Angkutan Udara, Kelaikudaraan & Pengoperasian Pesawat Udara']);


        // 3. BUAT AKUN KEPALA BERDASARKAN STRUKTUR ORGANISASI
        $defaultPassword = Hash::make('kepala123'); // Password default untuk testing

        // --- KEPALA KANTOR ---
        $kakan = $this->akunKepala([
            'name' => 'AGUSTONO, S.SOS. M.MTR',
            'nip' => '196908311991031001', // Spasi dihilangkan agar mudah untuk login
            'email' => 'kakan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Kantor',
            'bagian_bidang_id' => null,
            'sub_bagian_seksi_id' => null,
            'jatah_cuti' => 12,
        ], 'Kepala Kantor');

        // --- AKUN PLT (PELAKSANA TUGAS) KEPALA KANTOR ---
        // Akun tersendiri dengan role & level_jabatan yang sama persis seperti Kepala Kantor,
        // sehingga otomatis menerima notifikasi dan bisa menyetujui pengajuan di step 6.
        $plt = $this->akunKepala([
            'name' => 'PLT Pengganti Kepala Kantor',
            'nip' => '123456789', // Spasi dihilangkan agar mudah untuk login
            'email' => 'akunplt@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Kantor',
            'bagian_bidang_id' => null,
            'sub_bagian_seksi_id' => null,
            'jatah_cuti' => 12,
        ], 'Kepala Kantor');

        // --- KEPALA BAGIAN TATA USAHA ---
        $kabagTu = $this->akunKepala([
            'name' => 'DIAN WAHYUDI. M. SI',
            'nip' => '198002202000121003',
            'email' => 'kabag.tu@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => null,
        ], 'Kepala TU');

        // --- KEPALA SUB BAGIAN (TU) ---
        $kasubKeuangan = $this->akunKepala([
            'name' => 'MASRUKHIN. A.MD',
            'nip' => '197710151999031002',
            'email' => 'kasub.keuangan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => $subKeuangan->id,
        ], 'Kepala Sub-Bagian');

        $kasubKepegawaian = $this->akunKepala([
            'name' => 'DIAH YUNIATI, S.KOM, M.SC',
            'nip' => '198306132006042001',
            'email' => 'kasub.kepegawaian@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $tu->id,
            'sub_bagian_seksi_id' => $subKepegawaian->id,
        ], 'Kepala Sub-Bagian');

        // --- KEPALA BIDANG ---
        $kabidPelayanan = $this->akunKepala([
            'name' => 'ERWIN DWI PURNOMO, S.T., M.SC',
            'nip' => '198007302006041001',
            'email' => 'kabid.pelayanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => null,
        ], 'Kepala Bidang');

        $kabidKeamanan = $this->akunKepala([
            'name' => 'FUADANI, S.T., M.M',
            'nip' => '197011151993031001',
            'email' => 'kabid.keamanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Bagian/Bidang',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => null,
        ], 'Kepala Bidang');

        // --- KEPALA SEKSI ---
        $kasiFasilitas = $this->akunKepala([
            'name' => 'M. MEGA HERDIYANSYA S.SIT',
            'nip' => '198405222007121003',
            'email' => 'kasi.fasilitas@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => $seksiFasilitas->id,
        ], 'Kepala Seksi');

        $kasiPengoperasian = $this->akunKepala([
            'name' => 'CANDRA JAYA, SSIT, MM',
            'nip' => '197912052002121001',
            'email' => 'kasi.pengoperasian@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangPelayanan->id,
            'sub_bagian_seksi_id' => $seksiPengoperasian->id,
        ], 'Kepala Seksi');

        $kasiKeamanan = $this->akunKepala([
            'name' => 'ANDY HENDRA SURYAKA, ST., MM',
            'nip' => '197910202002121002',
            'email' => 'kasi.keamanan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => $seksiKeamanan->id,
        ], 'Kepala Seksi');

        $kasiAngkutan = $this->akunKepala([
            'name' => 'TRI RENGGO JOKO WAHONO, SE',
            'nip' => '197111031990091001',
            'email' => 'kasi.angkutan@otban3.com',
            'password' => $defaultPassword,
            'level_jabatan' => 'Kepala Seksi/Sub-Bagian',
            'bagian_bidang_id' => $bidangKeamanan->id,
            'sub_bagian_seksi_id' => $seksiAngkutan->id,
        ], 'Kepala Seksi');

        // Akun kepala baru memakai password awal yang sama -> wajib_ganti_kredensial = true
        // sudah diset di akunKepala(). Akun lama tidak di-reset ulang tiap seeding.

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