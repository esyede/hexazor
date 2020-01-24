<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;
use System\Console\Console;
use System\Support\Str;

class MakeModel extends GeneralFile
{
    protected $signature = 'make:model {:name} {--migration} {--seeder}';
    protected $description = 'Create a new model class';
    protected $type = 'Model';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        parent::handle();
        
        $name = $this->getArgument('name');
        
        if ($this->getOption('migration')) {
            $name = strtolower(Str::plural($name));
            Console::call('make:migration', 'create_'.$name.'_table', ['--create' => $name]);
        }

        if ($this->getOption('seeder')) {
            $name = Str::title(Str::plural($name));
            Console::call('make:seeder', $name.'TableSeeder', null);
        }
    }
    
    /**
     * Ambil path file stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return system_path('Console/stubs/make/model.stub');
    }

    /**
     * Ambil root nammespace default.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Bangun konten kelas menggunakan template dari file stub.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        return parent::buildClass($name);
    }
}
