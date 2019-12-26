<?php

namespace System\Console\Commands\Migrate;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Database\Migrations\Migrator;

class Migrate extends Command
{
    protected $signature = 'migrate';
    protected $description = 'Run the database migrations';
    protected $migrator;

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
        if (!$this->hasOption('force')) {
            if (!$this->confirmToProceed()) {
                return;
            }
        }

        $this->prepareDatabase();
        $this->migrator->run();

        $notes = $this->migrator->getNotes();

        foreach ($notes as $note) {
            $this->plain($note);
        }

        if ($this->hasOption('seed')) {
            $this->execute('db:seed --force=true');
        }
    }

    /**
     * Siapkan (install) tabel repository jika belum ada.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (!$this->migrator->repositoryExists()) {
            $this->execute('migrate:install');
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
