<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Generators\GeneralFile;
use System\Support\Str;

class MakeCommand extends GeneralFile
{
    protected $signature = 'make:command {:name}';
    protected $description = 'Create a new console command';
    protected $type = 'Command';

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
        return system_path('Console/stubs/make/command.stub');
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
        return $rootNamespace.'\\Console\\Commands';
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
        $signature = Str::replaceFirst('App\\Console\\Commands\\', '', $name);
        $signature = Str::kebab($signature);
        $replace = [
            'CommandSignature' => 'custom:'.$signature,
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }
}
