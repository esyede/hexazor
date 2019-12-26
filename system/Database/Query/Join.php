<?php

namespace System\Database\Query;

defined('DS') or exit('No direct script access allowed.');

class Join
{
    public $type;

    public $table;

    public $clauses = [];

    /**
     * Buat instance query join baru.
     *
     * @param string $type
     * @param string $table
     */
    public function __construct($type, $table)
    {
        $this->type = $type;
        $this->table = $table;
    }

    /**
     * Tambahkan klausa ON.
     *
     * @param string $column1
     * @param string $operator
     * @param string $column2
     * @param string $connector
     *
     * @return $this
     */
    public function on($column1, $operator, $column2, $connector = 'AND')
    {
        $this->clauses[] = compact('column1', 'operator', 'column2', 'connector');

        return $this;
    }

    /**
     * Tambahkan klausa OR ON.
     *
     * @param string $column1
     * @param string $operator
     * @param string $column2
     *
     * @return $this
     */
    public function orOn($column1, $operator, $column2)
    {
        return $this->on($column1, $operator, $column2, 'OR');
    }
}
