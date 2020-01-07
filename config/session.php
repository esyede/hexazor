<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Session Driver
    |--------------------------------------------------------------------------
    |
    | Nama driver session yang digunakan oleh aplikasi Anda. Karena HTTP
    | sifatnya stateless, session digunakan untuk mensimulasikan "state" di
    | seluruh request yang dibuat oleh user. Dengan kata lain, ini adalah
    | bagaimana aplikasi mengetahui siapa sih sebenarnya Anda ini.
    |
    | Pilihan driver: 'cookie', 'file', 'database'.
    |
    */

    'driver' => 'cookie',

    /*
    |--------------------------------------------------------------------------
    | Session Database
    |--------------------------------------------------------------------------
    |
    | Tabel database tempat session harus disimpan.
    | Opsi ini digunakan saat Anda memilih 'database' sebagai driver session.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Garbage Collection Probability
    |--------------------------------------------------------------------------
    |
    | Beberapa driver session memerlukan pembersihan manual dari session yang
    | telah kadaluwarsa. Opsi ini menentukan probabilitas pengumpulan
    | sampah session untuk setiap request yang diberikan ke aplikasi.
    |
    | Misalnya, nilai defaultnya memiliki peluang 2% untuk setiap request yang
    | diberikan ke aplikasi. Sesuaikan ini dengan kebutuhan Anda.
    |
    */

    'sweepage' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Jumlah menit session aktif sebelum ia kadaluwarsa.
    |
    */

    'lifetime' => 60,

    /*
    |--------------------------------------------------------------------------
    | Session Expiration On Close
    |--------------------------------------------------------------------------
    |
    | Menentukan apakah session akan kedaluwarsa ketika browser ditutup.
    |
    */

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Nama cookie untuk session.
    |
    */

    'cookie' => 'hexazor_session',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | Path dimana cookie session diletakkan.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | Domain dimana cookie session diletakkan.
    |
    */

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Session Cookie
    |--------------------------------------------------------------------------
    |
    | Tentukan apakah cookie hanya boleh dikirimkan melalui HTTPS.
    |
    */

    'secure' => false,
];
