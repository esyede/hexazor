<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

abstract class Migration implements MigrationInterface
{
    // Jalankan migrasi
    abstract public function up();

    // Balikkan migrasi
    abstract public function down();
}
