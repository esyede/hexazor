<?php

namespace System\Console\Generators;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Libraries\Storage\Storage;
use System\Support\Str;

class GeneralFile extends Command
{
    protected $type;

    protected static $storage;

    /**
     * Ambil path file stub.
     *
     * @return string
     */
    protected function getStub()
    {
        // ..
    }

    /**
     * Jalankan proses pembuatan file.
     *
     * @param string $input
     *
     * @return void
     */
    public function handle()
    {
        static::$storage = new Storage();

        $input = $this->getArgument('name');

        if (is_null($input)) {
            $this->writeline('The [name] argument is mandatory.');
            return false;
        }

        $name = $this->qualifyClass($input);
        $path = $this->getPath($name);

        if (!$this->getOption('force') && $this->alreadyExists($input)) {
            $this->writeline(Str::singular($this->type).' already exists!');
            return false;
        }

        $this->makeDirectory($path);
        static::$storage->put($path, $this->sortImports($this->buildClass($name)));
        $this->writeline(Str::singular($this->type).' created successfully.');
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
        $name = ltrim($name, '\\/');
        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Ambil root namespace default.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    protected function alreadyExists($rawName)
    {
        return static::$storage->exists($this->getPath($this->qualifyClass($rawName)));
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
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return app_path(str_replace('\\', '/', $name).'.php');
    }

    /**
     * Buat subdirectory untuk file kelas hasil generate (jika belum ada).
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!is_dir(dirname($path))) {
            static::$storage->makeDirectory(dirname($path), 0777, true);
        }

        return $path;
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
        $stub = static::$storage->get($this->getStub());
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Ganti tag dummy namespace di file stub.
     *
     * @param string &$stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            [$this->getNamespace($name), $this->rootNamespace()],
            $stub
        );

        return $this;
    }

    /**
     * Ambil namespace kelas dari nama yang diberikan.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Ganti nama dummy class dari file stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Ururkan import file menurut alfabet.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode(PHP_EOL, trim($match['imports']));
            sort($imports);

            return str_replace(trim($match['imports']), implode(PHP_EOL, $imports), $stub);
        }

        return $stub;
    }

    /**
     * Ambil root namespace default.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'App\\';
    }
}
