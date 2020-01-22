<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Session Driver
    |--------------------------------------------------------------------------
    |
    | Nama driver session yang digunakan oleh aplikasi Anda. Karena HTTP
    | sifatnya state-less, session digunakan untuk mensimulasikan "state" di
    | seluruh request yang dibuat oleh user. Dengan kata lain, ini adalah cara
    | bagaimana aplikasi mengetahui siapa sih sebenarnya Anda ini?
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
    | Nama tabel tempat session harus disimpan. Opsi ini digunakan saat
    | Anda memilih 'database' sebagai session driver.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Garbage Collection Probability
    |--------------------------------------------------------------------------
    |
    | Beberapa driver session memerlukan pembersihan manual dari session yang
    | telah kadaluwarsa. Opsi ini menentukan probabilitas pengumpulan sampah
    | session untuk setiap request yang diberikan ke aplikasi.
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
    | Opsi untuk menentukan apakah session akan kadaluwarsa ketika user
    | menutup browser miliknya.
    |
    */

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Nama cookie untuk session. Silahkan ubah jika dirasa perlu.
    |
    */

    'cookie' => 'hexazor_session',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | Path dimana cookie session diletakkan. Silahkan ubah jika dirasa perlu.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | Domain dimana cookie session diletakkan. Silahkan ubah jika dirasa perlu.
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
