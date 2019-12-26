<?php

namespace System\Console\Commands\Make;

use System\Console\Generators\GeneralFile;

class MakeCommand extends GeneralFile
{
    protected $signature = 'make:command {name}';
    protected $description = 'Create a new console command.';
    protected $type = 'Command';

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
        $replace = [
            'CommandSignature' => 'custom:'.$this->generateRandomWord(),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * Buat kata acak untuk mengisi signature command.
     *
     * @param int $length
     *
     * @return string
     */
    protected function generateRandomWord($length = 6)
    {
        $word = '';
        $vowels = ['a', 'e', 'i', 'o', 'u'];
        $consonants = [
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
        ];

        $max = $length / 2;

        for ($i = 1; $i <= $max; $i++) {
            $word .= $consonants[rand(0, 19)];
            $word .= $vowels[rand(0, 4)];
        }

        return $word;
    }
}
