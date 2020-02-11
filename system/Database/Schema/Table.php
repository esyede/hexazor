<?php

namespace System\Database\Schema;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Magic;

class Table
{
    public $name;
    public $connection;
    public $engine;
    public $columns = [];
    public $commands = [];

    /**
     * Buat instance schema table baru.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Tunjukkan bahwa tabel harus dibuat.
     *
     * @return \System\Support\Magic
     */
    public function create()
    {
        return $this->command(__FUNCTION__);
    }

    /**
     * Buat primary key baru.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function primary($columns, $name = null)
    {
        return $this->key(__FUNCTION__, $columns, $name);
    }

    /**
     * Buat unique index baru.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function unique($columns, $name = null)
    {
        return $this->key(__FUNCTION__, $columns, $name);
    }

    /**
     * Buat fulltext index baru.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function fulltext($columns, $name = null)
    {
        return $this->key(__FUNCTION__, $columns, $name);
    }

    /**
     * Buat index baru.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function index($columns, $name = null)
    {
        return $this->key(__FUNCTION__, $columns, $name);
    }

    /**
     * Tambahkan foreign key constraint baru.
     *
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function foreign($columns, $name = null)
    {
        return $this->key(__FUNCTION__, $columns, $name);
    }

    /**
     * Buat perintah untuk pembuatan index.
     *
     * @param string       $type
     * @param string|array $columns
     * @param string       $name
     *
     * @return \System\Support\Magic
     */
    public function key($type, $columns, $name)
    {
        $columns = (array) $columns;

        if (is_null($name)) {
            $name = str_replace(['-', '.'], '_', $this->name);
            $name = $name.'_'.implode('_', $columns).'_'.$type;
        }

        return $this->command($type, compact('name', 'columns'));
    }

    /**
     * Rename tabel.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function rename($name)
    {
        return $this->command(__FUNCTION__, compact('name'));
    }

    /**
     * Drop tabel.
     *
     * @return \System\Support\Magic
     */
    public function drop()
    {
        return $this->command(__FUNCTION__);
    }

    /**
     * Drop kolom.
     *
     * @param string|array $columns
     */
    public function dropColumn($columns)
    {
        $columns = (array) $columns;

        return $this->command(__FUNCTION__, ['columns' => $columns]);
    }

    /**
     * Drop primary key.
     *
     * @param string $name
     */
    public function dropPrimary($name = null)
    {
        return $this->dropKey(__FUNCTION__, $name);
    }

    /**
     * Drop unique index.
     *
     * @param string $name
     */
    public function dropUnique($name)
    {
        return $this->dropKey(__FUNCTION__, $name);
    }

    /**
     * Drop fulltext index.
     *
     * @param string $name
     */
    public function dropFulltext($name)
    {
        return $this->dropKey(__FUNCTION__, $name);
    }

    /**
     * Drop index.
     *
     * @param string $name
     */
    public function dropIndex($name)
    {
        return $this->dropKey(__FUNCTION__, $name);
    }

    /**
     * Drop foreign key constraint.
     *
     * @param string $name
     */
    public function dropForeign($name)
    {
        return $this->dropKey(__FUNCTION__, $name);
    }

    /**
     * Drop index key (tipe apa saja).
     *
     * @param string $name
     * @param mixed  $type
     */
    protected function dropKey($type, $name)
    {
        return $this->command($type, compact('name'));
    }

    /**
     * Tambahkan kolom integer auto-increment.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function increments($name)
    {
        return $this->integer($name, true);
    }

    /**
     * Tambahkan kolom string.
     *
     * @param string $name
     * @param int    $length
     *
     * @return \System\Support\Magic
     */
    public function string($name, $length = 200)
    {
        return $this->column(__FUNCTION__, compact('name', 'length'));
    }

    /**
     * Tambahkan kolom integer.
     *
     * @param string $name
     * @param bool   $increment
     *
     * @return \System\Support\Magic
     */
    public function integer($name, $increment = false)
    {
        return $this->column(__FUNCTION__, compact('name', 'increment'));
    }

    /**
     * Tambahkan kolom float.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function float($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Tambahkan kolom desimal.
     *
     * @param string $name
     * @param int    $precision
     * @param int    $scale
     *
     * @return \System\Support\Magic
     */
    public function decimal($name, $precision, $scale)
    {
        return $this->column(__FUNCTION__, compact('name', 'precision', 'scale'));
    }

    public function boolean($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Tambahkan kolom timestamps (created_at, updated_at).
     */
    public function timestamps()
    {
        $this->date('created_at');
        $this->date('updated_at');
    }

    // public function softDeletes()
    // {
    // 	$this->date('deleted_at');
    // }

    /**
     * Tambahkan kolom datetime.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function date($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Tambahkan kolom timestamp.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function timestamp($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Tambahkan kolom text.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function text($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Tambahkan kolom blob.
     *
     * @param string $name
     *
     * @return \System\Support\Magic
     */
    public function blob($name)
    {
        return $this->column(__FUNCTION__, compact('name'));
    }

    /**
     * Set koneksi database untuk operasi tabel.
     *
     * @param string $connection
     *
     * @return bool
     */
    public function on($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Cek apakah schema punya perintah pembuatan tabel.
     *
     * @return bool
     */
    public function creating()
    {
        $this->commands = (array) $this->commands;

        foreach ($this->commands as $key => $value) {
            if ('create' === $value->type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buat instance perintah baru.
     *
     * @param string $type
     * @param array  $parameters
     *
     * @return \System\Support\Magic
     */
    protected function command($type, $parameters = [])
    {
        $parameters = (array) $parameters;
        $parameters = array_merge(compact('type'), $parameters);

        return $this->commands[] = new Magic($parameters);
    }

    /**
     * Buat instance perintah baru untuk operasi kolom.
     *
     * @param string $type
     * @param array  $parameters
     *
     * @return \System\Support\Magic
     */
    protected function column($type, $parameters = [])
    {
        $parameters = (array) $parameters;
        $parameters = array_merge(compact('type'), $parameters);

        return $this->columns[] = new Magic($parameters);
    }
}
