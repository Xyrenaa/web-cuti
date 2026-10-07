<?php

use App\Models\User;
use App\Support\Captcha;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->withSession([Captcha::SESSION_KEY => Captcha::payload('12345')])->post('/login', [
        'nip' => $user->nip,
        'password' => 'password',
        'captcha' => '12345',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->withSession([Captcha::SESSION_KEY => Captcha::payload('12345')])->post('/login', [
        'nip' => $user->nip,
        'password' => 'wrong-password',
        'captcha' => '12345',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('NIP dengan spasi atau titik tetap bisa dipakai login', function () {
    $user = User::factory()->create(['nip' => '199505052020011005']);

    $this->withSession([Captcha::SESSION_KEY => Captcha::payload('12345')])->post('/login', [
        'nip' => '19950505 202001 1 005',
        'password' => 'password',
        'captcha' => '12345',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('email tidak lagi bisa dipakai untuk login', function () {
    $user = User::factory()->create();

    $this->withSession([Captcha::SESSION_KEY => Captcha::payload('12345')])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'captcha' => '12345',
    ])->assertSessionHasErrors('nip');

    $this->assertGuest();
});