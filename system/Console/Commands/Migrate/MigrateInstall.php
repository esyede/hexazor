<?php

namespace System\Console\Commands\Migrate;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class MigrateInstall extends Command
{
    protected $signature = 'migrate:install';

    protected $description = 'Install migration repository table';

    /**
     * Tangani command ini.
     */
    public function handle()
    {
        $migrator = new Migrator();

        if ($migrator->getRepos()->repositoryExists()) {
            return $this->error('Migration repository already exists!');
        } else {
            $migrator->getRepos()->createRepos();

            return $this->plain('Migration repository created successfully.');
        }
    }
}
