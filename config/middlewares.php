<?php

defined('DS') or exit('No direct script access allowed.');

return [
    /*
    |--------------------------------------------------------------------------
    | Global Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware yang didaftarkan pada bagian ini akan dieksekusi secara
    | otomatis pada semua rute di aplikasi Anda.
    |
    */

    'globals' => [
        // 'test' => 'App\Http\Middlewares\TestMiddleware',
    ],

    /*
    |--------------------------------------------------------------------------
    | Local Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware yang didaftarkan pada bagian ini hanya akan dieksekusi jika
    | Anda memanggilnya, pemanggilan bisa dilakukan melalui definisi routes
    | maupun dari dalam controller.
    |
    */

    'locals' => [
        // 'auth' => 'App\Http\Middlewares\AuthMiddleware',
    ],
];
