<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Driver Otentikasi Default
    |--------------------------------------------------------------------------
    |
    | Hexazor menggunakan sistem berbasis driver yang fleksibel untuk menangani
    | otentikasi. Anda bebas mendaftarkan driver Anda sendiri menggunakan
    | method Auth::extend(). Tentu saja, beberapa driver sudah disediakan
    | untuk menangani otentikasi dasar secara sederhana dan mudah.
    |
    | Pilihan driver: 'default', 'model'.
    |
    */

    'driver' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Nama Model Otentikasi
    |--------------------------------------------------------------------------
    |
    | Saat driver otentikasi default di set ke 'model', Anda dapat menentukan
    | model yang harus dianggap sebagai model "User". Model ini akan digunakan
    | untuk meng-otentikasi pengguna aplikasi Anda.
    |
    */

    'model' => '\App\User',

    /*
    |--------------------------------------------------------------------------
    | Nama Tabel Otentikasi
    |--------------------------------------------------------------------------
    |
    | Saat driver otentikasi default di set ke 'default', tabel database yang
    | digunakan untuk memuat pengguna dapat ditentukan di sini. Tabel inilah
    | yang digunakan oleh query builder untuk meng-otentikasi pengguna
    | pada aplikasi Anda.
    |
    */

    'table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'username'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "username" untuk user Anda. Biasanya diberi nama "username"
    | atau "email". Tentu saja, Anda bebas menamainya sesuai kebutuhan.
    |
    */

    'username' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'password'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "password" untuk user Anda. Biasanya diberi nama 'password'
    | atau 'kata_sandi' atau lainnya. Tentu saja, Anda juga bebas menamainya.
    |
    */

    'password' => 'password',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'remember_token'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "remember_token" untuk user Anda. Kolom ini digunakan untuk
    | fitur 'remember me'. Anda juga bebas mengganti namanya.
    |
    */

    'remember' => 'remember_token',
];
