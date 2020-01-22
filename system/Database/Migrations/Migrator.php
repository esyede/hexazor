<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Libraries\Storage\Storage;
use System\Support\Str;

class Migrator
{
    protected $repos;
    protected $storage;
    protected $notes = [];

    public function __construct()
    {
        $this->storage = new Storage();
        $this->repos = new Repos($this->getMigrationTable());
    }

    public function run()
    {
        $this->notes = [];
        $path = database_path('migrations/');
        $files = $this->getMigrationFiles($path);
        $ran = $this->repos->getRan();

        $migrations = array_diff($files, $ran);
        $this->requireFiles($path, $migrations);
        $this->runMigrationList($migrations);
    }

    public function runMigrationList($migrations)
    {
        if (0 == count($migrations)) {
            $this->note('Nothing to migrate.');

            return;
        }

        $batch = $this->repos->getNextBatchNumber();

        foreach ($migrations as $file) {
            $this->runUp($file, $batch);
        }
    }

    protected function runUp($file, $batch)
    {
        $migration = $this->resolve($file);

        $migration->up();
        $this->repos->log($file, $batch);

        $this->note("Migrated: $file");
    }

    public function rollback()
    {
        $this->notes = [];
        $migrations = $this->repos->getLast();

        if (0 == count($migrations)) {
            $this->note('Done. Nothing to rollback.');
            return count($migrations);
        }

        foreach ($migrations as $migration) {
            $migration = (object) $migration;
            $this->runDown($migration);
        }

        return count($migrations);
    }

    protected function runDown($migration)
    {
        $file = $migration->migration;
        $instance = $this->resolve($file);
        $instance->down();
        $this->repos->delete($migration);
        $this->note("Rolled back: $file");
    }

    public function getMigrationFiles($path)
    {
        $files = $this->storage->glob($path.'*_*.php');

        if (false === $files) {
            return [];
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);

        sort($files);

        return $files;
    }

    public function requireFiles($path, array $files)
    {
        foreach ($files as $file) {
            $this->storage->requireOnce($path.$file.'.php');
        }
    }

    public function resolve($file)
    {
        $path = database_path('migrations/'.$file).'.php';
        $this->storage->requireOnce($path);

        $file = implode('_', array_slice(explode('_', $file), 4));
        $class = Str::studly($file);

        return new $class();
    }

    protected function note($message)
    {
        $this->notes[] = $message;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getRepos()
    {
        return $this->repos;
    }

    public function repositoryExists()
    {
        return $this->getRepos()->repositoryExists();
    }

    public function getMigrationTable()
    {
        return Config::get('database.migration_table');
    }
}
