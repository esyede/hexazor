<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;

class MakeMiddleware extends GeneralFile
{
    protected $signature = 'make:middleware {:name}';
    protected $description = 'Create a new middleware class';
    protected $type = 'Middleware';

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
        return system_path('Console/stubs/make/middleware.stub');
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
        return $rootNamespace.'\\Http\\Middleware';
    }
}
