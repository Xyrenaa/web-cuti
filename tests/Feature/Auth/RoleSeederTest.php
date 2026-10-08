<?php

use App\Models\BagianBidang;
use App\Models\JenisCuti;
use App\Models\PengajuanCuti;
use App\Models\SubBagianSeksi;
use App\Models\User;
use Database\Seeders\JenisCutiSeeder;
use Database\Seeders\RoleSeeder;

test('RoleSeeder aman dijalankan berulang kali: tidak error dan tidak ada data ganda', function () {
    $this->seed(RoleSeeder::class);

    $hitung = fn () => [User::count(), BagianBidang::count(), SubBagianSeksi::count()];
    $awal = $hitung();

    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect($hitung())->toBe($awal);
});

test('seeding ulang tidak me-reset password yang sudah diganti pegawai', function () {
    $this->seed(RoleSeeder::class);

    $kakan = User::where('nip', '196908311991031001')->first();
    $kakan->forceFill([
        'email' => 'agustono@gmail.com',
        'password' => 'RahasiaBaru#2026',
        'wajib_ganti_kredensial' => false,
    ])->save();
    $hashBaru = $kakan->fresh()->password;

    $this->seed(RoleSeeder::class);

    $kakan->refresh();
    expect($kakan->email)->toBe('agustono@gmail.com')
        ->and($kakan->password)->toBe($hashBaru)
        ->and($kakan->wajib_ganti_kredensial)->toBeFalse();
});

test('akun PLT punya role Kepala Kantor dan wajib ganti kredensial di login pertama', function () {
    $this->seed(RoleSeeder::class);

    $plt = User::where('nip', '123456789')->first();

    expect($plt)->not->toBeNull()
        ->and($plt->hasRole('Kepala Kantor'))->toBeTrue()
        ->and($plt->level_jabatan)->toBe('Kepala Kantor')
        ->and($plt->wajib_ganti_kredensial)->toBeTrue()
        ->and(User::role('Kepala Kantor')->count())->toBe(2);
});

test('akun PLT bisa menyetujui pengajuan di meja Kepala Kantor (step 6) dan sesudahnya Kakan tidak bisa lagi', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(JenisCutiSeeder::class);

    $pemohon = User::where('email', 'pegawai@cuti.com')->first();
    $plt = User::where('nip', '123456789')->first();
    $kakan = User::where('nip', '196908311991031001')->first();

    $pengajuan = PengajuanCuti::create([
        'kode_pengajuan' => 'CT-TEST-0001',
        'user_id' => $pemohon->id,
        'jenis_cuti_id' => JenisCuti::first()->id,
        'tanggal_mulai' => now()->addDays(10)->toDateString(),
        'tanggal_selesai' => now()->addDays(11)->toDateString(),
        'durasi_hari' => 2,
        'alasan' => 'Keperluan keluarga',
        'status_pengajuan' => 'Menunggu Kepala Kantor',
        'approval_step' => 6,
    ]);

    // PLT sudah ganti kredensial (kalau belum, middleware memang menahan semua halaman)
    $plt->forceFill(['wajib_ganti_kredensial' => false])->save();
    $kakan->forceFill(['wajib_ganti_kredensial' => false])->save();

    $this->actingAs($plt)->put(route('kepala.approval.approve', $pengajuan->id))
        ->assertSessionHas('success');
    expect($pengajuan->fresh()->approval_step)->toBe(7);

    // Kakan menekan setelah PLT: sudah bukan mejanya lagi, step tidak maju dua kali
    $this->actingAs($kakan)->put(route('kepala.approval.approve', $pengajuan->id))
        ->assertSessionHas('error');
    expect($pengajuan->fresh()->approval_step)->toBe(7);
});