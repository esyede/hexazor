<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class MigrateReset extends Command
{
    protected $signature = 'migrate:reset {--force}';
    protected $description = 'Rollback all database migrations';

    protected static $migrator;

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        static::$migrator = new Migrator();

        if (!static::$migrator->getRepos()->repositoryExists()) {
            $repository = static::$migrator->getMigrationTable();
            $this->writeline('Migration repository does not exists: '.$repository);
            return false;
        }

        if (!$this->getOption('force')) {
            if (!$this->confirmation($this->description.'?')) {
                return false;
            }
        }

        while (true) {
            $count = static::$migrator->rollback();

            foreach (static::$migrator->getNotes() as $note) {
                $this->writeline($note);
            }

            if (0 == $count) {
                break;
            }
        }
    }
}
