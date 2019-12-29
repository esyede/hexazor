<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Only Cookie
    |--------------------------------------------------------------------------
    |
    | Jika di set ke TRUE, cookie hanya dapat diakses melalui protokol HTTP.
    | Ini berarti bahwa cookie tidak akan dapat diakses oleh bahasa scripting,
    | seperti JavaScript. Disarankan bahwa pengaturan ini dapat secara
    | efektif membantu mengurangi pencurian identitas melalui serangan XSS
    | (meskipun tidak didukung oleh semua browser), tetapi klaim tersebut maih
    | sering diperdebatkan. Referensi:
    | https://www.php.net/manual/en/session.configuration.php
    |
    */

    'cookie_httponly'  => true,

    /*
    |--------------------------------------------------------------------------
    | Session Use Only Cookie
    |--------------------------------------------------------------------------
    |
    | Secara default, aplikasi harus menggunakan cookie untuk Session ID. Jika
    | opsi ini dimatikan, modul session di PHP akan menggunakan value Session ID
    | yang dibuat oleh GET / POST / URL. Disarankan untuk mengaktifkan opsi ini
    | agar aplikasi Anda tetap mendapat tambahan proteksi. Referensi:
    | https://www.php.net/manual/en/session.configuration.php
    |
    */

    'use_only_cookies' => true,

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Lifetime
    |--------------------------------------------------------------------------
    |
    | Lamanya waktu aktif sebuah cookie (dalam satuan detik) sebelum dihapus
    | oleh garbage collector. Referensi:
    | https://www.php.net/manual/en/session.configuration.php
    |
    */

    'lifetime' => 3600,
];
