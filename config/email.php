<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | SMTP Host
    |--------------------------------------------------------------------------
    |
    | Host dari smtp server Anda. Secara default, Hexazor menggunakan Mailtrap
    | untuk keperluan testing, namun Anda boleh mengubahnya sesuai kebutuhan.
    |
    */

    'host' => 'smtp.mailtrap.io',

    /*
    |--------------------------------------------------------------------------
    | SMTP Protocol
    |--------------------------------------------------------------------------
    |
    | Protokol SMTP yang Anda gunakan, bisa diisi dengan 'tcp', 'ssl' atau
    | asalkan mendukung pengiriman email via SMTP.
    |
    */

    'protocol' => 'tcp',

    /*
    |--------------------------------------------------------------------------
    | SMTP Port
    |--------------------------------------------------------------------------
    |
    | Port SMTP yang Anda gunakan untuk pengiriman email, sesuaikan dengan
    | akun yang Anda miliki.
    |
    */

    'port' => 587,

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | Username dari akun SMTP Anda. Sesuaikan dengan akun yang Anda miliki.
    |
    */

    'username' => '',

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    |
    | Password dari akun SMTP Anda. Sesuaikan dengan akun yang Anda miliki.
    |
    */

    'password' => '',

    /*
    |--------------------------------------------------------------------------
    | Mailer Daeman
    |--------------------------------------------------------------------------
    |
    | Nama custom untuk mailer daemon. Ubahlah jika Anda ingin menggunakan
    | nama lain. Jika dikosongkan, namanya akan kembali menjadi 'Hexazor Mail'
    |
    */

    'mailer' => 'Hexazor Mail',

    /*
    |--------------------------------------------------------------------------
    | Character Set
    |--------------------------------------------------------------------------
    |
    | Set encoding karakter yang akan digunakan untuk pengiriman email Anda.
    | UTF-8 adalah character set standar yang lazim digunakan, akan tetapi,
    | Anda boleh mengubahnya ke character set lain.
    |
    */

    'charset' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Connection Time-out
    |--------------------------------------------------------------------------
    |
    | Lamanya waktu library untuk melakukan koneksi ke server. Jika waktu
    | ini sudah terlewat, koneksi akan dihentikan. connection timeout
    | standar yang lazim digunakan adalah 100000 mili detik.
    |
    */

    'connection_timeout' => 100000,

    /*
    |--------------------------------------------------------------------------
    | Response Time-out
    |--------------------------------------------------------------------------
    |
    | Lamanya waktu library untuk menunggu response dari server. Jika waktu
    | ini sudah terlewat, proses pengiriman akan dihentikan. response timeout
    | standar yang lazim digunakan adalah 100000 mili detik.
    |
    */

    'response_timeout' => 100000,
];
