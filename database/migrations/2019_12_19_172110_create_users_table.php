<?php

defined('DS') or exit('No direct script access allowed.');

use System\Database\Migrations\Migration;
use System\Database\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Nama tabel target migrasi (ubah sesuai kebutuhan).
     *
     * @var string
     */
    protected static $targetTable = 'users';

    /**
     * Jalankan proses migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::$targetTable, function ($table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->string('remember');
            $table->timestamps();
        });
    }

    /**
     * Balikkan proses migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(static::$targetTable);
    }
}
