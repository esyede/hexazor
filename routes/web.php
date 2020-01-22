<?php

defined('DS') or exit('No direct script access allowed.');

use System\Core\Route;

/*
|--------------------------------------------------------------------------
| Routing Web
|--------------------------------------------------------------------------
|
| Daftar definisi rute web untuk aplikasi Anda. Beri tahu Hexazor
| method dan URI apa yang harus ditanggapi serta sebutkan controller mana
| yang harus menanganinya. Anda juga boleh menggunakan Closure jika hanya
| ingin menampilkan view.
|
*/

Route::get('/', 'HomeController@index')->name('home');
Route::get('/test', 'HomeController@test')->name('test');
