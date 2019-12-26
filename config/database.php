<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Profiler Database
    |--------------------------------------------------------------------------
    |
    | Secara default, SQL, binding, dan waktu eksekusi dicatat untuk Anda.
    | Mereka dapat diambil menggunakan method DB::profile(). Tetapi, di
    | beberapa situasi, Anda kadang ingin mematikan pencatatan ini
    | misalya saat Anda melakukan equery database yang berat. Anda bisa
    | melakukannya melalui opsi ini.
    |
    */

    'profile' => true,

    /*
    |--------------------------------------------------------------------------
    | Nama Tabel Migrasi
    |--------------------------------------------------------------------------
    |
    | Nama tabel migrasi. Tabel inilah yang akan menjadi tempat penyimpanan
    | catatan migrasi ketika Anda menjalankan command -command migrasi database
    | seperti 'hexazor migrate', 'hexazor migrate:rollback' dll.
    |
    |
    */

    'migration_table' => 'hexazor_migrations',

    /*
    |--------------------------------------------------------------------------
    | Fetch Style PDO
    |--------------------------------------------------------------------------
    |
    | Secara default, hasil query database akan di-return sbagai instance dari
    | object stdClass; akan tetapi, jika Anda lebih suka me-return array
    | daripada object, bisa saja. Melalui opsi ini Anda bisa mengontrol
    | fetch style PDO dari query database yang dijalankan oleh aplikasi Anda.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Koneksi Database Default
    |--------------------------------------------------------------------------
    |
    | Ini adalah nama dari koneksi database default Anda. Koneksi inilah yang
    | akan digunakan sebagai koneksi standar untuk seluruh operasi database
    | kecuali Anda menentukan nama lain saat melakukan operasi database.
    | Nama koneksi ini harus tercantum dalam list koneksi database di bawah.
    |
    */

    'default' => 'sqlite',

    /*
    |--------------------------------------------------------------------------
    | List Koneksi Database
    |--------------------------------------------------------------------------
    |
    | Semua koneksi basis data yang digunakan oleh aplikasi Anda. Pada umumnya,
    | aplikasi Anda hanya akan menggunakan satu koneksi; namun, Anda bebas
    | untuk menentukan berapa banyak koneksi yang hendak Anda tangani.
    |
    | Semua operasi database di Hexazor dilakukan melalui fasilitas PDO PHP,
    | jadi pastikan untuk menginstall driver PDO sesuai database pilihan Anda.
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
