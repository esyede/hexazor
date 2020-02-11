<?php

namespace System\Database\Schema\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Schema\Table;
use System\Support\Magic;

class SQLite extends Grammar
{
    /**
     * Buat sql statement untuk perintah pembuatan tabel.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $command
     *
     * @return string
     */
    public function create(Table $table, Magic $command)
    {
        $columns = implode(', ', $this->columns($table));
        $sql = 'CREATE TABLE '.$this->wrap($table).' ('.$columns;

        $primary = null;

        foreach ($table->commands as $key => $value) {
            if ('primary' === $value->type) {
                $primary = $value;
                break;
            }
        }

        if (!is_null($primary)) {
            $columns = $this->columnize($primary->columns);
            $sql .= ", PRIMARY KEY ({$columns})";
        }

        return $sql .= ')';
    }

    /**
     * Buat sql statement untuk perintah modifikasi tabel.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $command
     *
     * @return string
     */
    public function add(Table $table, Magic $command)
    {
        $columns = $this->columns($table);
        $columns = array_map(function ($column) {
            return 'ADD COLUMN '.$column;
        }, $columns);

        foreach ($columns as $column) {
            $sql[] = 'ALTER TABLE '.$this->wrap($table).' '.$column;
        }

        return (array) $sql;
    }

    /**
     * Buat definisi kolom individual untuk tabel.
     *
     * @param \System\Database\Schema\Table $table
     *
     * @return string
     */
    protected function columns(Table $table)
    {
        $columns = [];
        
        foreach ($table->columns as $column) {
            $sql = $this->wrap($column).' '.$this->type($column);
            $elements = ['nullable', 'defaults', 'incrementer'];
        
            foreach ($elements as $element) {
                $sql .= $this->{$element}($table, $column);
            }

            $columns[] = $sql;
        }

        return $columns;
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah nullable.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    protected function nullable(Table $table, Magic $column)
    {
        return ' NULL';
    }

    /**
     * Ambil sintaks sql untuk menentukan nilai default pada kolom.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    protected function defaults(Table $table, Magic $column)
    {
        if (!is_null($column->default)) {
            return ' DEFAULT '.$this->wrap($this->defaultValue($column->default));
        }
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah primary key dan auto-incremant.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    protected function incrementer(Table $table, Magic $column)
    {
        if ('integer' === $column->type && $column->increment) {
            return ' PRIMARY KEY AUTOINCREMENT';
        }
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah unique index.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function unique(Table $table, Magic $command)
    {
        return $this->key($table, $command, true);
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah fulltext index.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function fulltext(Table $table, Magic $command)
    {
        $columns = $this->columnize($command->columns);

        return 'CREATE VIRTUAL TABLE '.$this->wrap($table)." USING fts4({$columns})";
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah index standar.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function index(Table $table, Magic $command)
    {
        return $this->key($table, $command);
    }

    /**
     * Ambil sintaks sql untuk pembuatan index baru.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     * @param string                        $type
     * @param mixed                         $unique
     *
     * @return string
     */
    protected function key(Table $table, Magic $command, $unique = false)
    {
        $columns = $this->columnize($command->columns);
        $create = ($unique) ? 'CREATE UNIQUE' : 'CREATE';

        return $create." INDEX {$command->name} ON ".$this->wrap($table)." ({$columns})";
    }

    /**
     * Ambil sintaks sql untuk operasi rename tabel.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function rename(Table $table, Magic $command)
    {
        return 'ALTER TABLE '.$this->wrap($table).' RENAME TO '.$this->wrap($command->name);
    }

    /**
     * Ambil sintaks sql untuk operasi drop unique index.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropUnique(Table $table, Magic $command)
    {
        return $this->dropKey($table, $command);
    }

    /**
     * Ambil sintaks sql untuk operasi drop index standar.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropIndex(Table $table, Magic $command)
    {
        return $this->dropKey($table, $command);
    }

    /**
     * Ambil sintaks sql untuk operasi drop key.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    protected function dropKey(Table $table, Magic $command)
    {
        return 'DROP INDEX '.$this->wrap($command->name);
    }

    /**
     * Definisi tipe data string.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeString(Magic $column)
    {
        return 'VARCHAR';
    }

    /**
     * Definisi tipe data integer.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeInteger(Magic $column)
    {
        return 'INTEGER';
    }

    /**
     * Definisi tipe data float.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeFloat(Magic $column)
    {
        return 'FLOAT';
    }

    /**
     * Definisi tipe data decimal.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeDecimal(Magic $column)
    {
        return 'FLOAT';
    }

    /**
     * Definisi tipe data integer.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeBoolean(Magic $column)
    {
        return 'INTEGER';
    }

    /**
     * Definisi tipe data date.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeDate(Magic $column)
    {
        return 'DATETIME';
    }

    /**
     * Definisi tipe data timestamp.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeTimestamp(Magic $column)
    {
        return 'DATETIME';
    }

    /**
     * Definisi tipe data text.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeText(Magic $column)
    {
        return 'TEXT';
    }

    /**
     * Definisi tipe data blob.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeBlob(Magic $column)
    {
        return 'BLOB';
    }
}
