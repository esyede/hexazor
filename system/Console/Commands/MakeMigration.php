<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\MigrationFile;

class MakeMigration extends MigrationFile
{
    protected $signature = 'make:migration {:name} {--table}';
    protected $description = 'Create a new migration class';
    protected $creator;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle()
    {
        $name =$this->getArgument('name');

        if (is_null($name)) {
            $this->writeline('The [name] argument is mandatory.');
            return false;
        }

        $table = $this->getOption('table');
        $create = $this->getOption('create');

        if (is_null($table) && is_string($create)) {
            $table = $create;
            $create = true;
        }

        $this->writeMigration($name, $table, $create);
    }

    /**
     * Buat file migration.
     *
     * @param string $name
     * @param string $table
     * @param bool   $create
     *
     * @return void
     */
    protected function writeMigration($name, $table, $create)
    {
        $path = $this->getMigrationPath();
        $file = pathinfo($this->create($name, $table, $create), PATHINFO_FILENAME);
        $this->writeline("Created Migration: $file");
    }

    /**
     * Ambil path folder penyimpanan file migrasi.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return database_path('migrations/');
    }
}
