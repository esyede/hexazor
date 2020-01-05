<?php

namespace System\Console\Commands\Make;

use System\Console\Generators\GeneralFile;

class MakeController extends GeneralFile
{
    protected $signature = 'make:controller {name}';
    protected $description = 'Create a new controller class';
    protected $type = 'Controller';

    /**
     * Ambil path file stub.
     *
     * @return string
     */
    protected function getStub()
    {
        return system_path('Console/stubs/make/controller.stub');
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
        return $rootNamespace.'\\Http\\Controllers';
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
