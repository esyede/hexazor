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
            $table->increments('id');

            //

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
