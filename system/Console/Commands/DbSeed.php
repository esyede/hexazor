<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Debugger\Debugger;
use System\Support\Str;

class DbSeed extends Command
{
    protected $signature = 'db:seed {--class=DatabaseSeeder} {--force}';
    protected $description = 'Seed the database with records';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        if (!is_object($this->getSeeder())) {
            return false;
        }

        if (!$this->getOption('force')) {
            if (!$this->confirmation($this->description.'?')) {
                return false;
            }
        }

        $this->getSeeder()->run();
        $this->writeline('Operation finished.');
    }

    /**
     * Include dan instance objek seeder.
     *
     * @return object
     */
    protected function getSeeder()
    {
        $class = $this->getOption('class');

        if (!is_file(database_path('seeds/'.$class.'.php'))) {
            $this->newline();
            $this->writeline('Seeder class not found: '.$class);
            return false;
        }

        require_once database_path('seeds/'.$class.'.php');
        $class = new $class();

        return $class;
    }
}
