<?php

namespace System\Console\Commands\Migrate;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class MigrateReset extends Command
{
    protected $signature = 'migrate:reset';
    protected $description = 'Rollback all database migrations';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->migrator = new Migrator();
    }

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->migrator->getRepos()->repositoryExists()) {
            return $this->plain(
                'Migration repository does not exists: '.
                $this->migrator->getMigrationTable()
            );
        }

        if (!$this->hasOption('force')) {
            if (!$this->confirmToProceed()) {
                return;
            }
        }

        while (true) {
            $count = $this->migrator->rollback();

            foreach ($this->migrator->getNotes() as $note) {
                $this->plain($note);
            }

            if (0 == $count) {
                break;
            }
        }
    }

    /**
     * Konfirmasi untuk melanjutkan proses.
     *
     * @return bool
     */
    public function confirmToProceed()
    {
        return $this->confirm($this->description.'?');
    }
}
