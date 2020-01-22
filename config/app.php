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
    | sebagai secret salt untuk enkripsi cookie, cache, session dan Crypt.
    |
    | Anda boleh mengubahnya dengan command 'key:generate' di Hexazor Console
    | jika dirasa perlu. Namun, disarankan untuk tidak mengubahnya secara manual
    | agar aplikasi Anda tetap aman.
    |
    */

    'application_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | URL ke halaman muka aplikasi Anda. Biasanya, ini akan menjadi URL halaman
    | rumah situs Anda. Jika dikosongkan, maka Hexazor mencoba menebaknya
    | dengan bantuan $_SERVER['HTTP_HOST'] dan $_SERVER['SCRIPT_NAME'].
    |
    | Contoh isian base url:
    | https://situsku.com/ atau jika aplikasi berada didalam subfolder:
    | https://situsku.com/blog/
    |
    */

    'base_url' => '',

    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | Zona waktu default untuk aplikasi Anda. Anda dapat mengubahnya mengikuti
    | aturan timezone string di PHP. Referensinya dapat dilihat disini:
    | https://www.php.net/manual/en/timezones.php
    |
    | Jika opsi ini dikosongkan, ia akan kembali ke timezone default yaitu UTC.
    |
    */

    'default_timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Bahasa default untuk aplikasi Anda. Nama ini harus sesuai dengan nama
    | salah satu subfolder yang berada di folder 'resources/lang/'. Tentu Anda
    | boleh menambahkan bahasa lain, namun secara default, Hexazor hanya
    | menyediakan 2 subfolder yaitu 'en' (english) dan 'id' (bahasa indonesia)
    |
    */

    'default_language' => 'en',

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
    | library yang Anda install via composer.
    |
    */

    'composer_autoload' => true,
];
