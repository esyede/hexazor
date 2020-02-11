<?php

namespace System\Console\Commands\Migrate;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Console\Console;
use System\Support\Str;

class MigrateRefresh extends Command
{
    protected $signature = 'migrate:refresh {--seed} {--seeder=DatabaseSeeder}';
    protected $description = 'Reset and re-run all migrations';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirmation($this->description.'?')) {
            return false;
        }

        Console::call('migrate:reset', null, ['--force' => true]);
        Console::call('migrate', null, ['--force' => true]);

        if ($this->needsSeeding()) {
            $this->runSeeder();
        }
    }

    /**
     * Cek apakah user memerintahkan untuk menjalankan seeding.
     *
     * @return bool
     */
    protected function needsSeeding()
    {
        return $this->getOption('seed') || filled($this->getOption('seeder'));
    }

    /**
     * Panggil command 'db:seed'.
     *
     * @return void
     */
    protected function runSeeder()
    {
        $seeder = $this->getOption('seeder');
        $seeder = filled($seeder) ? $seeder : 'DatabaseSeeder';
        Console::call('db:seed', null, ['--class' => $seeder, '--force' => true]);
    }
}
