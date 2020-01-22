<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;

class MakeListener extends GeneralFile
{
    protected $signature = 'make:listener {:name}';
    protected $description = 'Create a new listener class';
    protected $type = 'Listener';

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
        return system_path('Console/stubs/make/listener.stub');
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
        return $rootNamespace.'\\Http\\Listeners';
    }
}
