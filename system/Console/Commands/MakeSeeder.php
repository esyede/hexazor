<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;

class MakeSeeder extends GeneralFile
{
    protected $signature = 'make:seeder {:name}';
    protected $description = 'Create a new seeder class';
    protected $type = 'Seeder';

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
        $this->ensureDatabaseSeederClassExists();
        $this->ensureUsersTableSeederClassExists();
        parent::handle();
    }

    /**
     * Pastikan kelas DatabaseSeeder ada, untuk parent seeder.
     *
     * @return void
     */
    protected function ensureDatabaseSeederClassExists()
    {
        $file = $this->getPath('DatabaseSeeder');

        if (!is_file($file)) {
            $stub = system_path('Console/stubs/make/database_seeder.stub');
            $stub = file_get_contents($stub);

            return false !== file_put_contents($file, $stub, LOCK_EX);
        }
    }


    protected function ensureUsersTableSeederClassExists()
    {
        $file = $this->getPath('UsersTableSeeder');

        if (!is_file($file)) {
            $stub = system_path('Console/stubs/make/users_table_seeder.stub');
            $stub = file_get_contents($stub);

            return false !== file_put_contents($file, $stub, LOCK_EX);
        }
    }

    /**
     * Ambil path file stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return system_path('Console/stubs/make/seeder.stub');
    }

    /**
     * Ubah string nama kelas menjadi fullpath (absolut).
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        return database_path('seeds/'.$name.'.php');
    }

    /**
     * Ubah string nama file menjadi FQCN (fully qualified class name).
     *
     * @param string $name
     *
     * @return string
     */
    protected function qualifyClass($name)
    {
        return preg_replace('/[\W]/', '', $name);
    }
}
