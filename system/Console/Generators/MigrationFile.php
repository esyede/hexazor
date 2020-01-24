<?php

namespace System\Console\Generators;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use System\Console\Command;
use System\Libraries\Storage\Storage;
use System\Support\Str;

class MigrationFile extends Command
{
    protected $migrationPath;
    protected $postCreate = [];

    protected static $storage = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        if (is_null(static::$storage)) {
            static::$storage = new Storage();
        }

        $this->migrationPath = database_path('migrations/');
    }

    /**
     * Buat file migrasi.
     *
     * @param string $name
     * @param string $table
     * @param bool   $create
     *
     * @return string
     */
    public function create($name, $table = null, $create = false)
    {
        $path = $this->getPath($name);
        $stub = $this->getStub($table, $create);

        $this->ensureMigrationPathExists();
        $this->ensureMigrationDoesntAlreadyExist($name);

        static::$storage->put($path, $this->populateStub($name, $stub, $table));
        $this->firePostCreateHooks();

        return $path;
    }

    /**
     * Ambil konten file stub.
     *
     * @param string $table
     * @param bool   $create
     *
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            $stub = 'blank.stub';
        } else {
            $stub = $create ? 'create.stub' : 'update.stub';
        }

        return static::$storage->get($this->getStubPath().$stub);
    }

    /**
     * Pastikan kelas migrasi belum ada (cegah dupilkat nama kelas).
     *
     * @param string $name
     *
     * @return bool
     */
    protected function ensureMigrationDoesntAlreadyExist($name)
    {
        $files = static::$storage->glob($this->migrationPath.'*.php');

        foreach ($files as $file) {
            static::$storage->requireOnce($file);
        }

        if (class_exists($className = $this->getClassName($name))) {
            $this->writeline("A {$className} class already exists.");
            exit();
        }
    }

    /**
     * Pastikan folder migration sudah dibuat.
     *
     * @return void
     */
    protected function ensureMigrationPathExists()
    {
        if (!is_dir($this->migrationPath)) {
            static::$storage->makeDirectory($this->migrationPath, 0777, true);
        }
    }

    /**
     * Ganti token - token di konten stub.
     *
     * @param string $name
     * @param string $stub
     * @param string $table
     *
     * @return string
     */
    protected function populateStub($name, $stub, $table)
    {
        $stub = str_replace('stubClassName', $this->getClassName($name), $stub);

        if (!is_null($table)) {
            $stub = str_replace('nama_tabel_anda', $table, $stub);
        }

        return $stub;
    }

    /**
     * Ubah nama file menjadi sudly-case.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }

    /**
     * Jalankan hook setelah file migrasi dibuat.
     *
     * @return void
     */
    protected function firePostCreateHooks()
    {
        if (!blank($this->postCreate)) {
            foreach ($this->postCreate as $callback) {
                call_user_func($callback);
            }
        }
    }

    /**
     * Daftarkan hook yang akan dijalankan setelah aksi create.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function afterCreate(Closure $callback)
    {
        $this->postCreate = array_merge($this->postCreate, $callback);
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
        return database_path('migrations/'.$this->getDatePrefix().'_'.$name.'.php');
    }

    /**
     * Buat prefix tanggal untuk nama file.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Ambil path file stub.
     *
     * @return string
     */
    public function getStubPath()
    {
        return system_path('console/stubs/make/migration/');
    }

    /**
     * Ambil objek kelas Storage.
     *
     * @return \System\Libraries\Storage\Storage
     */
    public function getStorage()
    {
        return static::$storage;
    }
}
