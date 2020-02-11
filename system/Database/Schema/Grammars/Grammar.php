<?php

namespace System\Database\Schema\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Grammar as BaseGrammar;
use System\Database\Schema\Table;
use System\Support\Magic;
use System\Support\Str;

abstract class Grammar extends BaseGrammar
{
    /**
     * Buat sql statement untuk perintah pembuatan foreign key.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $command
     *
     * @return string
     */
    public function foreign(Table $table, Magic $command)
    {
        $name = $command->name;
        $table = $this->wrap($table);
        $on = $this->wrapTable($command->on);
        $foreign = $this->columnize($command->columns);
        $referenced = $this->columnize((array) $command->references);

        $sql = "ALTER TABLE $table ADD CONSTRAINT $name ";
        $sql .= "FOREIGN KEY ($foreign) REFERENCES $on ($referenced)";

        if (!is_null($command->on_delete)) {
            $sql .= " ON DELETE {$command->on_delete}";
        }

        if (!is_null($command->on_update)) {
            $sql .= " ON UPDATE {$command->on_update}";
        }

        return $sql;
    }

    /**
     * Buat sql statement untuk perintah drop table.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $command
     *
     * @return string
     */
    public function drop(Table $table, Magic $command)
    {
        return 'DROP TABLE '.$this->wrap($table);
    }

    /**
     * Buat sql statement untuk perintah drop constraint.
     *
     * @param \System\Database\Schema\Table $table
     * @param \System\Support\Magic         $command
     *
     * @return string
     */
    protected function dropConstraint(Table $table, Magic $command)
    {
        return 'ALTER TABLE '.$this->wrap($table).' DROP CONSTRAINT '.$command->name;
    }

    /**
     * Wrap value dalam keyword identifier.
     *
     * @param string|\System\Database\Schema\Table $value
     *
     * @return string
     */
    public function wrap($value)
    {
        if ($value instanceof Table) {
            return $this->wrapTable($value->name);
        } elseif ($value instanceof Magic) {
            $value = $value->name;
        }

        return parent::wrap($value);
    }

    /**
     * Ambil definisi tipe data yang sesuai untuk kolom.
     *
     * @param \System\Support\Magic $column
     *
     * @return string
     */
    protected function type(Magic $column)
    {
        $columnType = 'type'.Str::studly($column->type);

        return $this->{$columnType}($column);
    }

    /**
     * Format value agar dapat digunakan dalam klausa sql DEFAULT ''.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function defaultValue($value)
    {
        if (is_bool($value)) {
            return intval($value);
        }

        return strval($value);
    }
}
