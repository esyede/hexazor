<?php

defined('DS') or exit('No direct script access allowed.');

use System\Database\Migrations\Migration;
use System\Database\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Jalankan proses migrasi.
     */
    public function up()
    {
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->string('remember_token');
            $table->timestamps();
        });
    }

    /**
     * Balikkan proses migrasi.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
