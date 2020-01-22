<?php

defined('DS') or exit('No direct script access allowed.');

use System\Database\Migrations\Migration;
use System\Database\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Jalankan proses migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function ($table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->string('username')->index();
            $table->string('password')->index();
            $table->string('email')->index();
            $table->integer('role_id')->unsigned()->index();
            $table->boolean('verified');
            $table->boolean('disabled');
            $table->boolean('deleted');

            $table->timestamps();
        });
    }


    /**
     * Balikkan poses migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
