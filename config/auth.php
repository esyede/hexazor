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
    | Saat menggunakan driver otentikasi 'model', Anda dapat menentukan model
    | yang harus dianggap sebagai model "User". Model ini akan digunakan untuk
    | meng-otentikasi pengguna aplikasi Anda.
    |
    */

    'model' => '\App\User',

    /*
    |--------------------------------------------------------------------------
    | Nama Tabel Otentikasi
    |--------------------------------------------------------------------------
    |
    | Saat menggunakan driver otentikasi 'default', tabel database yang
    | digunakan untuk memuat pengguna dapat ditentukan di sini. Tabel ini akan
    | digunakan oleh query builder untuk mengotentikasi dan meng-otentikasi
    | pengguna aplikasi Anda.
    |
    */

    'table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'username'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "username" untuk user Anda. Biasanya, ini bisa berupa "username"
    | atau "email". Tentu saja, Anda bebas mengubahnya sesuai kebutuhan.
    |
    */

    'username' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'password'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "password" untuk user Anda. Biasanya, ini bisa berupa "password"
    | atau "kata_sandi" atau lainnya. Tentu saja, Anda juga bebas mengubahnya.
    |
    */

    'password' => 'password',

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom 'remember_me'
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan nama kolom di database yang harus dianggap
    | sebagai "remember_me" untuk user Anda. Anda juga bebas mengubahnya.
    |
    */

    'remember' => 'remember_token',
];
