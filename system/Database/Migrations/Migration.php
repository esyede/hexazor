<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

abstract class Migration implements MigrationInterface
{
    abstract public function up();

    abstract public function down();
}
