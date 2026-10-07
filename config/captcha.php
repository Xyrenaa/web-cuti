<?php

return [

    /*
    | Saklar captcha login. Matikan HANYA untuk darurat/lokal (CAPTCHA_ENABLED=false),
    | misalnya kalau ekstensi PHP GD belum aktif di server.
    */
    'enabled' => env('CAPTCHA_ENABLED', true),

    // Jumlah angka yang harus diketik pengguna.
    'length' => 5,

    // Masa berlaku satu gambar captcha (detik).
    'ttl' => 300,
];