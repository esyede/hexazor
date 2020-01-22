<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;

class MakeModel extends GeneralFile
{
    protected $signature = 'make:model {:name}';
    protected $description = 'Create a new model class';
    protected $type = 'Model';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
