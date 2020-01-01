<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Application Key
    |--------------------------------------------------------------------------
    |
    | Application key ini akan terisi otomatis saat Anda membuka halaman depan
    | aplikasi Anda. Ini berisi mminimal 32 karakter acak yang digunakan
    | sebagai secret salt untuk enkripsi cookie, session dan library Crypt.
    |
    | Anda boleh mengubahnya dengan command 'key:generate' di Hexazor Console
    | jika dirasa perlu. Namun, disarankan untuk tidak mengubahnya secara manual
    | agar aplikasi Anda tetap aman.
    |
    */

    'application_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | Zona waktu default untuk aplikasi Anda. Anda dapat mengubahnya mengikuti
    | aturan tentang timezone string di PHP. Referensinya dapat dilihat disini:
    | https://www.php.net/manual/en/timezones.php
    |
    | Jika opsi ini tidak diisi, ia akan kembali ke timezone default yaitu UTC.
    |
    */

    'default_timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Composer Autoload
    |--------------------------------------------------------------------------
    |
    | Opsi untuk memuat secara otomatis file autoload.php milik Composer.
    | Jika opsi ini diaktifkan, Hexazor akan mecari dan memuat
    | file 'vendor/autoload.php' di dalam root folder aplikasi Anda.
    |
    | Hexazor hanya akan melakukan pencarian di root folder saja, sehingga jika
    | Anda menaruh file autoload composer selain di root folder, Anda perlu
    | memuatnya secara manual. Hal ini dikarenakan Hexazor perlu menambahkan
    | sebuah file htaccess kedalah folder 'vendor/' untuk memproteksi
    | library yang Anda install.
    |
    */

    'composer_autoload' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Bahasa default untuk aplikasi Anda. Nama ini harus sesuai dengan nama
    | salah satu subfolder yang berada di folder 'resources/lang/'. Tentu Anda
    | boleh menambahkan bahasa lain, namun secara default, Hexazor hanya
    | menyediakan 2 subfolder yaitu en (english) dan id (bahasa indonesia)
    |
    */

    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Cache Expiration
    |--------------------------------------------------------------------------
    |
    | Waktu maksimum sebelum sebuah cache di aplikasi Anda kadaluwarsa.
    | Defaultnya adalah 604800 (1 minggu, dalam detik). Anda boleh
    | mengubahnya sesuai kebutuhan.
    |
    */

    'cache_expiration' => 604800,
];
