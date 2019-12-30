<?php

namespace App\Http;

defined('DS') or exit('No direct script access allowed.');

class Kernel
{
    /**
     * Kelompok middleware global.
     * Middleware yang didaftarkan pada kelompok ini akan dieksekusi secara
     * otomatis pada semua rute di aplikasi Anda.
     *
     * @var array
     */
    public static $globalMiddlewareGroups = [
        // 'web' => [
        //     '\App\Http\Middleware\VerifyCsrfToken',
        // ],
    ];

    /**
     * Kelompok middleware lokal.
     * Middleware yang didaftarkan pada kelompok ini hanya akan dieksekusi jika
     * Anda memanggilnya, pemanggilan bisa dilakukan melalui definisi routes
     * maupun dari dalam controller.
     *
     * @var array
     */
    public static $localMiddlewareGroups = [
        'auth' => [
            '\App\Http\Middleware\Authenticate',
        ],
    ];

    /**
     * Perintah - perintah yang yang didaftarkan method ini
     * akan dieksekusi tepat setelah proses booting framework selesai.
     *
     * @return void
     */
    public static function boot()
    {
        // ...
    }
}
