<?php

namespace System\Console\Commands\Make;

use System\Console\Generators\GeneralFile;

class MakeSeeder extends GeneralFile
{
    protected $signature = 'make:seeder {name}';
    protected $description = 'Create a new seeder class.';
    protected $type = 'Seeder';

    /**
     * Tangani command ini.
     *
     * @return void
     */
    public function handle($input)
    {
        parent::handle($input);
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
