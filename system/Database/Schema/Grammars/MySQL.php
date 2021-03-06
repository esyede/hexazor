<?php

namespace System\Database\Schema\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Schema\Table;
use System\Support\Magic;

class MySQL extends Grammar
{
    public $wrapper = '`%s`';

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
        $sql = 'CREATE TABLE '.$this->wrap($table).' ('.$columns.')';

        if (!is_null($table->engine)) {
            $sql .= ' ENGINE = '.$table->engine;
        }

        return $sql;
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
        $columns = implode(', ', array_map(function ($column) {
            return 'ADD '.$column;
        }, $columns));

        return 'ALTER TABLE '.$this->wrap($table).' '.$columns;
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
            $elements = ['unsigned', 'nullable', 'defaults', 'incrementer'];
        
            foreach ($elements as $element) {
                $sql .= $this->{$element}($table, $column);
            }

            $columns[] = $sql;
        }

        return $columns;
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah unsigned.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    protected function unsigned(Table $table, Magic $column)
    {
        if ('integer' === $column->type && ($column->unsigned || $column->increment)) {
            return ' UNSIGNED';
        }
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
        return ($column->nullable) ? ' NULL' : ' NOT NULL';
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
            return " DEFAULT '".$this->defaultValue($column->default)."'";
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
            return ' AUTO_INCREMENT PRIMARY KEY';
        }
    }

    /**
     * Ambil sintaks sql untuk menunjukkan apakah value kolom adalah primary key.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function primary(Table $table, Magic $command)
    {
        return $this->key($table, $command->name(null), 'PRIMARY KEY');
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
        return $this->key($table, $command, 'UNIQUE');
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
        return $this->key($table, $command, 'FULLTEXT');
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
        return $this->key($table, $command, 'INDEX');
    }

    /**
     * Ambil sintaks sql untuk pembuatan index baru.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     * @param string                        $type
     *
     * @return string
     */
    protected function key(Table $table, Magic $command, $type)
    {
        $keys = $this->columnize($command->columns);
        $name = $command->name;

        return 'ALTER TABLE '.$this->wrap($table)." ADD {$type} {$name}({$keys})";
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
        return 'RENAME TABLE '.$this->wrap($table).' TO '.$this->wrap($command->name);
    }

    /**
     * Ambil sintaks sql untuk operasi drop kolom.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropColumn(Table $table, Magic $command)
    {
        $columns = array_map([$this, 'wrap'], $command->columns);
        $columns = implode(', ', array_map(function ($column) {
            return 'DROP '.$column;
        }, $columns));

        return 'ALTER TABLE '.$this->wrap($table).' '.$columns;
    }

    /**
     * Ambil sintaks sql untuk operasi drop primary key.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropPrimary(Table $table, Magic $command)
    {
        return 'ALTER TABLE '.$this->wrap($table).' DROP PRIMARY KEY';
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
     * Ambil sintaks sql untuk operasi drop fulltext index.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropFulltext(Table $table, Magic $command)
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
        return 'ALTER TABLE '.$this->wrap($table)." DROP INDEX {$command->name}";
    }

    /**
     * Ambil sintaks sql untuk operasi drop foreign key constraint.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $column
     *
     * @return string
     */
    public function dropForeign(Table $table, Magic $command)
    {
        return 'ALTER TABLE '.$this->wrap($table).' DROP FOREIGN KEY '.$command->name;
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
        return 'VARCHAR('.$column->length.')';
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
        return 'INT';
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
        return "DECIMAL({$column->precision}, {$column->scale})";
    }

    /**
     * Definisi tipe data boolean.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function typeBoolean(Magic $column)
    {
        return 'TINYINT(1)';
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
        return 'TIMESTAMP';
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
