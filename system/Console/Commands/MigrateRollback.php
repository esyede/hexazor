<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class MigrateRollback extends Command
{
    protected $signature = 'migrate:rollback';
    protected $description = 'Rollback the last database migration';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $migrator = new Migrator();

        if (!$migrator->getRepos()->repositoryExists()) {
            $repository = $migrator->getMigrationTable();
            $this->writeline('Migration repository does not exists: '.$repository);
            exit();
        }

        $migrator->rollback();
        $notes = $migrator->getNotes();

        foreach ($notes as $note) {
            $this->writeline($note);
        }
    }
}
