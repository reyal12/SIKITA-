<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Akun Default Administrator
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh AdminSeeder untuk membuat akun awal administrator klinik.
    | Nilai dapat dikonfigurasi melalui berkas .env.
    |
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator SIKITA'),
        'email' => env('ADMIN_EMAIL', 'admin@sikita.test'),
        'password' => env('ADMIN_PASSWORD', 'admin12345'),
    ],
];
