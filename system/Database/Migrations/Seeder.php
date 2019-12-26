<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Console;

class Seeder
{
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
        if (!(static::$console instanceof Console)) {
            static::$console = new Console();
        }

        static::$console->plain("Seeding: {$class}");

        $time = microtime(true);

        $this->resolve($class)->run();

        $time = round(microtime(true) - $time, 2);

        static::$console->plain("Seeded : {$class}   {$time}s");
        static::$console->newline();
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
            return $this->plain('Seeder class not found: '.$class);
        }

        require_once database_path('seeds/'.$class.'.php');

        return new $class();
    }
}
