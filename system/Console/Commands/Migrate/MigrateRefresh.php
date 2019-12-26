<?php

namespace System\Console\Commands\Migrate;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Support\Str;

class MigrateRefresh extends Command
{
    protected $signature = 'migrate:refresh';

    protected $description = 'Reset and re-run all migrations';

    /**
     * Tangani command ini.
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->addOption('force', true);
        $this->execute('migrate:reset');
        $this->execute('migrate');

        // if ($this->needsSeeding()) {
        //     $this->runSeeder();
        // }
    }

    /**
     * Cek apakah user memerintahkan untuk menjalankan seeding.
     *
     * @return bool
     */
    // protected function needsSeeding()
    // {
    //     return $this->hasOption('seed') || filled($this->option('seeder'));
    // }

    /**
     * Panggil command 'db:seed'.
     */
    // protected function runSeeder()
    // {
    //     $class = filled($this->option('seeder'))
    //         ? Str::studly($this->option('seeder'))
    //         : 'DatabaseSeeder';

    //     $this->execute('db:seed --class='.$class);
    // }

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
