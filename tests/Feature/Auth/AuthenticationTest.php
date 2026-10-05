<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'nip' => $user->nip,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'nip' => $user->nip,
        'password' => 'wrong-password',
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

    $this->post('/login', [
        'nip' => '19950505 202001 1 005',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('email tidak lagi bisa dipakai untuk login', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('nip');

    $this->assertGuest();
});