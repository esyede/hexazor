<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Profiler Database
    |--------------------------------------------------------------------------
    |
    | Secara default, seluruh query SQL, binding, dan waktu eksekusi query
    | akan dicatat oleh Hexazor. Anda dapat melihatnya pada debug bar ataupun
    | menggunakan method DB::profile().
    |
    | Tetapi, di beberapa situasi, Anda kadang ingin mematikan
    | fitur pencatatan ini, misalya saat Anda menjalankan query database yang
    | kompleks dan berat. Anda bisa melakukannya melalui opsi ini.
    |
    */

    'enable_profiler' => true,

    /*
    |--------------------------------------------------------------------------
    | Nama Tabel Migrasi
    |--------------------------------------------------------------------------
    |
    | Nama tabel migrasi. Tabel inilah yang akan menjadi tempat penyimpanan
    | catatan migrasi ketika Anda menjalankan command migrasi database
    | seperti 'hexazor migrate', 'hexazor migrate:rollback' dll.
    |
    */

    'migration_table' => 'hexazor_migrations',

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | Secara default, hasil query database akan direturn sebagai instance dari
    | object stdClass; akan tetapi, jika Anda lebih suka mereturn array
    | ketimbang object, bisa saja.
    |
    | Melalui opsi ini Anda bisa mengontrol fetch style PDO dari query database
    | yang dijalankan oleh aplikasi Anda.
    |
    */

    'fetch_style' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Koneksi Database Default
    |--------------------------------------------------------------------------
    |
    | Ini adalah nama dari koneksi database default Anda. Koneksi inilah yang
    | akan digunakan sebagai koneksi standar untuk seluruh operasi database,
    | kecuali Anda menentukan nama lain pada saat melakukan operasi database.
    | Nama koneksi ini harus tercantum dalam list koneksi database di bawah.
    |
    */

    'default' => 'sqlite',

    /*
    |--------------------------------------------------------------------------
    | List Koneksi Database
    |--------------------------------------------------------------------------
    |
    | List nama - nama koneksi database yang digunakan oleh aplikasi Anda.
    | Pada umumnya, aplikasi Anda hanya akan menggunakan satu koneksi; namun,
    | Anda bebas menentukan berapa banyak koneksi yang hendak Anda tangani.
    |
    | Semua operasi database di Hexazor dilakukan dengan bantuan PDO, jadi
    | pastikan untuk menginstall driver PDO sesuai database pilihan Anda.
    |
    */

    'connections' => [
        'mysql' => [
            'driver'   => 'mysql',
            'host'     => '127.0.0.1',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => 'application',
            'prefix'   => '',
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => '127.0.0.1',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => '127.0.0.1',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'prefix'   => '',
        ],
    ],
];
