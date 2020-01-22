<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Console\Console;
use System\Database\Migrations\Migrator;

class Migrate extends Command
{
    protected $signature = 'migrate {--force} {--seed}';
    protected $description = 'Run the database migrations';

    protected static $migrator;

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        static::$migrator = new Migrator();

        if (!$this->getOption('force')) {
            if (!$this->confirmation($this->description.'?')) {
                return false;
            }
        }

        $this->prepareDatabase();
        static::$migrator->run();

        $notes = static::$migrator->getNotes();

        foreach ($notes as $note) {
            $this->writeline($note);
        }

        $this->newline();

        if ($this->getOption('seed')) {
            Console::call('db:seed', null, ['--force' => true]);
        }
    }

    /**
     * Siapkan (install) tabel repository jika belum ada.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (!static::$migrator->repositoryExists()) {
            Console::call('migrate:install');
        }
    }
}
