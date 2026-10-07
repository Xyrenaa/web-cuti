<?php

use App\Models\User;
use App\Support\Captcha;

function sesiCaptcha(string $kode = '12345'): array
{
    return [Captcha::SESSION_KEY => Captcha::payload($kode)];
}

test('halaman login menampilkan gambar captcha dan menyimpan hash (bukan kode) di session', function () {
    $response = $this->get('/login');

    // @js() menulis '/' sebagai '\/', jadi cukup cek awalan data:image
    $response->assertOk()->assertSee('Gambar captcha angka', false)->assertSee('data:image', false);

    $tersimpan = session(Captcha::SESSION_KEY);
    expect($tersimpan)->toHaveKeys(['hash', 'exp']);
});

test('login ditolak kalau captcha kosong', function () {
    $user = User::factory()->create();

    $this->withSession(sesiCaptcha())->post('/login', [
        'nip' => $user->nip, 'password' => 'password',
    ])->assertSessionHasErrors('captcha');

    $this->assertGuest();
});

test('login ditolak kalau captcha salah, walau NIP dan password benar', function () {
    $user = User::factory()->create();

    $this->withSession(sesiCaptcha('12345'))->post('/login', [
        'nip' => $user->nip, 'password' => 'password', 'captcha' => '54321',
    ])->assertSessionHasErrors('captcha');

    $this->assertGuest();
});

test('captcha kedaluwarsa ditolak', function () {
    $user = User::factory()->create();

    $kedaluwarsa = ['hash' => Captcha::payload('12345')['hash'], 'exp' => time() - 1];

    $this->withSession([Captcha::SESSION_KEY => $kedaluwarsa])->post('/login', [
        'nip' => $user->nip, 'password' => 'password', 'captcha' => '12345',
    ])->assertSessionHasErrors('captcha');

    $this->assertGuest();
});

test('captcha hanya berlaku sekali pakai', function () {
    $user = User::factory()->create();

    // percobaan pertama: captcha benar tapi password salah -> kode hangus
    $this->withSession(sesiCaptcha())->post('/login', [
        'nip' => $user->nip, 'password' => 'salah', 'captcha' => '12345',
    ])->assertSessionHasErrors('nip');

    expect(session(Captcha::SESSION_KEY))->toBeNull();

    // kode yang sama dipakai lagi tanpa gambar baru -> ditolak
    $this->post('/login', [
        'nip' => $user->nip, 'password' => 'password', 'captcha' => '12345',
    ])->assertSessionHasErrors('captcha');

    $this->assertGuest();
});

test('captcha benar membuat login berhasil', function () {
    $user = User::factory()->create();

    $this->withSession(sesiCaptcha('00742'))->post('/login', [
        'nip' => $user->nip, 'password' => 'password', 'captcha' => '00742',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('tombol ganti gambar memberi gambar baru dan mengganti kode di session', function () {
    $this->withSession(sesiCaptcha())->getJson('/captcha/segarkan')
        ->assertOk()
        ->assertJsonStructure(['src']);

    // kode lama (12345) tidak lagi berlaku
    $user = User::factory()->create();
    $this->post('/login', [
        'nip' => $user->nip, 'password' => 'password', 'captcha' => '12345',
    ])->assertSessionHasErrors('captcha');
});

test('saat captcha dimatikan lewat config, login tidak meminta captcha', function () {
    config(['captcha.enabled' => false]);
    $user = User::factory()->create();

    $this->get('/login')->assertOk()->assertDontSee('Gambar captcha angka');

    $this->post('/login', ['nip' => $user->nip, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});