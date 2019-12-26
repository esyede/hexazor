<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

interface MigrationInterface
{
    public function up();

    public function down();
}
