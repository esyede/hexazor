<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class MigrateInstall extends Command
{
    protected $signature = 'migrate:install';
    protected $description = 'Install migration repository table';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $migrator = new Migrator();

        if ($migrator->getRepos()->repositoryExists()) {
            $this->writeline('Migration repository already exists.');
        } else {
            $migrator->getRepos()->createRepos();
            $this->writeline('Migration repository created successfully.');
        }
    }
}
