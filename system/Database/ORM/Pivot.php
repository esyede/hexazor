<?php

namespace System\Database\ORM;

defined('DS') or exit('No direct script access allowed.');

class Pivot extends Model
{
    public static $timestamps = true;

    protected $table;
    protected $connection;

    /**
     * Buat instance pivot table baru.
     *
     * @param string $table
     * @param string $connection
     */
    public function __construct($table, $connection = null)
    {
        $this->table = $table;
        $this->connection = $connection;

        parent::__construct([], true);
    }

    /**
     * Ambil nama pivot table.
     *
     * @return string
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * Ambil koneksi yang digunakan pada pivot table.
     *
     * @return string
     */
    public function connection()
    {
        return $this->connection;
    }
}
