<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Traits\Ask;
use System\Console\Traits\Writer;

class Seeder
{
    use Ask;
    use Writer;

    public static $console;

    /**
     * Jalankan logic seeder (di-override oleh child class).
     *
     * @return void
     */
    public function run()
    {
        // overide..
    }

    /**
     * Panggil kelas seeder dan panggil method 'run'-nya.
     *
     * @param string $class
     *
     * @return void
     */
    public function call($class)
    {
        if (is_object($object = $this->resolve($class))) {
            $this->writeline('Seeding: '.$class);
            $time = microtime(true);
            $object->run();
            $time = round(microtime(true) - $time, 2);
            $this->writeline('Seeded : '.$class.'   '.$time.'s');
            $this->newline();
        }
    }

    /**
     * Include dan instance kelas seeder.
     *
     * @param string $class
     *
     * @return object
     */
    protected function resolve($class)
    {
        if (!is_file(database_path('seeds/'.$class.'.php'))) {
            $this->writeline('Seeding: '.$class);
            $this->writeline('Halted : '.$class.' [seeder not found]');
            $this->newline();
            return false;
        }

        require_once database_path('seeds/'.$class.'.php');
        return new $class();
    }
}
