<?php

namespace System\Database\Query\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Query;

class SQLServer extends Grammar
{
    protected $wrapper = '[%s]';
    public $datetime = 'Y-m-d H:i:s.000';

    /**
     * Compile statemant SELECT dari instance kelas Query.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    public function select(Query $query)
    {
        $sql = parent::components($query);

        if ($query->offset > 0) {
            return $this->ansiOffset($query, $sql);
        }

        return $this->concatenate($sql);
    }

    /**
     * Compile statemant SELECT.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function selects(Query $query)
    {
        if (!is_null($query->aggregate)) {
            return;
        }

        $select = ($query->distinct) ? 'SELECT DISTINCT ' : 'SELECT ';

        if ($query->limit > 0 && $query->offset <= 0) {
            $select .= 'TOP '.$query->limit.' ';
        }

        return $select.$this->columnize($query->selects);
    }

    /**
     * Hasilkan SQL standar ANSI untuk klausa offset.
     *
     * @param \System\Database\Query $query
     * @param array                  $components
     *
     * @return array
     */
    protected function ansiOffset(Query $query, $components)
    {
        if (!isset($components['orderings'])) {
            $components['orderings'] = 'ORDER BY (SELECT 0)';
        }

        $orderings = $components['orderings'];
        $components['selects'] .= ", ROW_NUMBER() OVER ({$orderings}) AS RowNum";
        unset($components['orderings']);

        $start = $query->offset + 1;

        if ($query->limit > 0) {
            $finish = $query->offset + $query->limit;
            $constraint = "BETWEEN {$start} AND {$finish}";
        } else {
            $constraint = ">= {$start}";
        }

        $sql = $this->concatenate($components);

        return "SELECT * FROM ($sql) AS TempTable WHERE RowNum {$constraint}";
    }

    /**
     * Compile klausa LIMIT.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function limit(Query $query)
    {
        return '';
    }

    /**
     * Compile klausa OFFSET.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function offset(Query $query)
    {
        return '';
    }
}
