<?php

namespace System\Database\Migrations;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Database;
use System\Database\Schema;

class Repos
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function getRan()
    {
        return $this->table()->lists('migration');
    }

    public function getLast()
    {
        $query = $this->table()->where('batch', '=', $this->getLastBatchNumber());

        return $query->orderBy('migration', 'desc')->get();
    }

    public function log($file, $batch)
    {
        $record = ['migration' => $file, 'batch' => $batch];
        $this->table()->insert($record);
    }

    public function delete($migration)
    {
        $this->table()->where('migration', '=', $migration->migration)->delete();
    }

    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    public function getLastBatchNumber()
    {
        return $this->table()->max('batch');
    }

    public function createRepos()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function ($table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }
    }

    public function repositoryExists()
    {
        return Schema::hasTable($this->table);
    }

    protected function table()
    {
        return Database::table($this->table);
    }
}
