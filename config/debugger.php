<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | Jika mode ini diaktifkan, setiap error yang terjadi akan menyebabkan
    | eksekusi aplikasi Anda akan langsung dihentikan; jika sebaliknya, maka
    | aplikasi akan tetap berjalan, error hanya akan ditampilkan di debug bar.
    |
    */

    'strict_mode' => true,

    /*
    |--------------------------------------------------------------------------
    | Scream!
    |--------------------------------------------------------------------------
    |
    | Opsi untuk menonaktifkan operator @ (diam!) sehingga notice dan warning
    | tidak lagi disembunyikan oleh PHP.
    |
    */

    'scream' => true,

    /*
    |--------------------------------------------------------------------------
    | Show Debug Bar
    |--------------------------------------------------------------------------
    |
    | Opsi untuk menampilkan / menyembunyikan debug bar pada mode development.
    |
    */

    'show_debugbar' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum Depth
    |--------------------------------------------------------------------------
    |
    | Sebarapa dalam level array / object yang harus ditampilkan ketika Anda
    | memanggil perintah dd(), bd() dan dump() ?
    |
    */

    'max_depth' => 5,

    /*
    |--------------------------------------------------------------------------
    | Maximum Depth
    |--------------------------------------------------------------------------
    |
    | Sebarapa banyak string yang harus ditampilkan ketika Anda memanggil
    | perintah dd(), bd() dan dump() ?
    |
    */

    'max_length' => 300,

    /*
    |--------------------------------------------------------------------------
    | Show Location
    |--------------------------------------------------------------------------
    |
    | Apakah lokasi file juga harus ditampilkan ketika Anda  memanggil perintah
    | dd(), bd() dan dump() ?
    |
    */

    'show_location' => false,

    /*
    |--------------------------------------------------------------------------
    | Error Email
    |--------------------------------------------------------------------------
    |
    | Isi dengan alamat email Anda jika Anda ingin menerima notifokasi error
    | pada aplikasi Anda.
    |
    */

    'email' => '',
];
