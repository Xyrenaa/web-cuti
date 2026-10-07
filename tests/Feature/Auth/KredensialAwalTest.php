<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function pegawaiBaru(): User
{
    return User::factory()->create([
        'nip' => '199001012015011001',
        'email' => '199001012015011001@otban3.com',
        'password' => Hash::make('password123'),
        'wajib_ganti_kredensial' => true,
    ]);
}

test('pop-up tampil di dashboard untuk akun yang wajib ganti kredensial', function () {
    $this->actingAs(pegawaiBaru())->get('/dashboard')
        ->assertOk()
        ->assertSee('Amankan Akun Cuti Anda');
});

test('halaman selain dashboard dialihkan selama kredensial belum diganti', function () {
    $user = pegawaiBaru();

    $this->actingAs($user)->get('/pengajuan')->assertRedirect(route('dashboard'));
    $this->actingAs($user)->post('/pengajuan', [])->assertRedirect(route('dashboard'));
});

test('email dan password berhasil diganti lalu akses terbuka', function () {
    $user = pegawaiBaru();

    $this->actingAs($user)->put('/kredensial-awal', [
        'email' => 'Budi@Gmail.com',
        'email_confirmation' => 'budi@gmail.com',
        'password' => 'RahasiaBaru#2026',
        'password_confirmation' => 'RahasiaBaru#2026',
    ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->email)->toBe('budi@gmail.com')
        ->and($user->wajib_ganti_kredensial)->toBeFalse()
        ->and(Hash::check('RahasiaBaru#2026', $user->password))->toBeTrue();

    $this->get('/pengajuan')->assertOk();
});

test('password baru tidak boleh sama dengan password awal', function () {
    $this->actingAs(pegawaiBaru())->put('/kredensial-awal', [
        'email' => 'budi@gmail.com',
        'email_confirmation' => 'budi@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrorsIn('kredensialAwal', 'password');
});

test('email sementara atau yang sudah dipakai akun lain ditolak', function () {
    User::factory()->create(['email' => 'dipakai@gmail.com']);
    $user = pegawaiBaru();

    foreach (['199001012015011001@otban3.com', 'dipakai@gmail.com'] as $email) {
        $this->actingAs($user)->put('/kredensial-awal', [
            'email' => $email,
            'email_confirmation' => $email,
            'password' => 'RahasiaBaru#2026',
            'password_confirmation' => 'RahasiaBaru#2026',
        ])->assertSessionHasErrorsIn('kredensialAwal', 'email');
    }
});

test('akun yang sudah selesai tidak bisa memakai endpoint ini lagi', function () {
    $user = User::factory()->create(['wajib_ganti_kredensial' => false]);

    $this->actingAs($user)->put('/kredensial-awal', [
        'email' => 'lain@gmail.com',
        'email_confirmation' => 'lain@gmail.com',
        'password' => 'RahasiaBaru#2026',
        'password_confirmation' => 'RahasiaBaru#2026',
    ])->assertRedirect(route('dashboard'));

    expect($user->fresh()->email)->not->toBe('lain@gmail.com');
});