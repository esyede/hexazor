<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;

class DbSeed extends Command
{
    protected $signature = 'db:seed';
    protected $description = 'Seed the database with records.';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->hasOption('force')) {
            if ($this->hasOption('class')) {
                $this->plain('Seeding: '.$this->option('class'));
                $this->newline();
            } else {
                if (!$this->confirmToProceed()) {
                    return;
                }
            }
        }

        $this->getSeeder()->run();
        $this->plain('Database seeding completed.');
    }

    /**
     * Include dan instance objek seeder.
     *
     * @return object
     */
    protected function getSeeder()
    {
        $class = filled($this->option('class')) ? $this->option('class') : 'DatabaseSeeder';

        if (!is_file(database_path('seeds/'.$class.'.php'))) {
            $this->plain('Seeder class not found: '.$class);
            exit();
        }

        require_once database_path('seeds/'.$class.'.php');
        $class = new $class();

        return $class;
    }

    /**
     * Konfirmasi untu melnjutkan operasi.
     *
     * @return void
     */
    protected function confirmToProceed()
    {
        return $this->confirm($this->description.'?');
    }
}
