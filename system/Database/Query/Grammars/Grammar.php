<?php

namespace System\Database\Query\Grammars;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Grammar as BaseGrammar;
use System\Database\Query;

class Grammar extends BaseGrammar
{
    public $datetime = 'Y-m-d H:i:s';

    protected $components = [
        'aggregate', 'selects', 'from', 'joins', 'wheres',
        'groupings', 'havings', 'orderings', 'limit', 'offset',
    ];

    /**
     * Compile sql SELECT dari instance kelas Query.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    public function select(Query $query)
    {
        return $this->concatenate($this->components($query));
    }

    /**
     * Bangun SQL untuk tiap-tiap komponen query.
     *
     * @param \System\Database\Query $query
     *
     * @return array
     */
    final protected function components($query)
    {
        foreach ($this->components as $component) {
            if (!is_null($query->$component)) {
                $sql[$component] = call_user_func([$this, $component], $query);
            }
        }

        return (array) $sql;
    }

    /**
     * Gabungkan array tiap-tiap segmen SQL, buang yang kosong.
     *
     * @param array $components
     *
     * @return string
     */
    final protected function concatenate($components)
    {
        return implode(' ', array_filter($components, function ($value) {
            return '' !== (string) $value;
        }));
    }

    /**
     * Compile klausa SELECT.
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

        return $select.$this->columnize($query->selects);
    }

    /**
     * Compile klausa SELECT aggregate.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function aggregate(Query $query)
    {
        $column = $this->columnize($query->aggregate['columns']);

        if ($query->distinct && '*' !== $column) {
            $column = 'DISTINCT '.$column;
        }

        return 'SELECT '.$query->aggregate['aggregator'].'('.$column.') AS '.$this->wrap('aggregate');
    }

    /**
     * Compile klausa FROM.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function from(Query $query)
    {
        return 'FROM '.$this->wrapTable($query->from);
    }

    /**
     * Compile klausa JOIN.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function joins(Query $query)
    {
        foreach ($query->joins as $join) {
            $table = $this->wrapTable($join->table);

            $clauses = [];
            foreach ($join->clauses as $clause) {
                extract($clause);
                $column1 = $this->wrap($column1);
                $column2 = $this->wrap($column2);
                $clauses[] = "{$connector} {$column1} {$operator} {$column2}";
            }

            $search = ['AND ', 'OR '];
            $clauses[0] = str_replace($search, '', $clauses[0]);
            $clauses = implode(' ', $clauses);
            $sql[] = "{$join->type} JOIN {$table} ON {$clauses}";
        }

        return implode(' ', $sql);
    }

    /**
     * Compile klausa WHERE.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    final protected function wheres(Query $query)
    {
        if (is_null($query->wheres)) {
            return '';
        }

        foreach ($query->wheres as $where) {
            $sql[] = $where['connector'].' '.$this->{$where['type']}($where);
        }

        if (isset($sql)) {
            return 'WHERE '.preg_replace('/AND |OR /', '', implode(' ', $sql), 1);
        }
    }

    /**
     * Compile klausa WHERE bersarang.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereNested($where)
    {
        return '('.substr($this->wheres($where['query']), 6).')';
    }

    /**
     * Compile klausa WHERE standar.
     *
     * @param array $where
     *
     * @return string
     */
    protected function where($where)
    {
        $parameter = $this->parameter($where['value']);

        return $this->wrap($where['column']).' '.$where['operator'].' '.$parameter;
    }

    /**
     * Compile klausa WHERE IN.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereIn($where)
    {
        $parameters = $this->parameterize($where['values']);

        return $this->wrap($where['column']).' IN ('.$parameters.')';
    }

    /**
     * Compile klausa WHERE NOT IN.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereNotIn($where)
    {
        $parameters = $this->parameterize($where['values']);

        return $this->wrap($where['column']).' NOT IN ('.$parameters.')';
    }

    /**
     * Compile klausa WHERE BETWEEN.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereBetween($where)
    {
        $min = $this->parameter($where['min']);
        $max = $this->parameter($where['max']);

        return $this->wrap($where['column']).' BETWEEN '.$min.' AND '.$max;
    }

    /**
     * Compile klausa WHERE NOT BETWEEN.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereNotBetween($where)
    {
        $min = $this->parameter($where['min']);
        $max = $this->parameter($where['max']);

        return $this->wrap($where['column']).' NOT BETWEEN '.$min.' AND '.$max;
    }

    /**
     * Compile klausa WHERE IS NULL.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereNull($where)
    {
        return $this->wrap($where['column']).' IS NULL';
    }

    /**
     * Compile klausa WHERE IS NOT NULL.
     *
     * @param array $where
     *
     * @return string
     */
    protected function whereNotNull($where)
    {
        return $this->wrap($where['column']).' IS NOT NULL';
    }

    /**
     * Compile klausa WHERE mentah.
     *
     * @param array $where
     *
     * @return string
     */
    final protected function whereRaw($where)
    {
        return $where['sql'];
    }

    /**
     * Compile klausa GROUP BY.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function groupings(Query $query)
    {
        return 'GROUP BY '.$this->columnize($query->groupings);
    }

    /**
     * Compile klausa HAVING.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function havings(Query $query)
    {
        if (is_null($query->havings)) {
            return '';
        }

        foreach ($query->havings as $having) {
            $sql[] = 'AND '.$this->wrap($having['column']).' '.
                $having['operator'].' '.
                $this->parameter($having['value']);
        }

        return 'HAVING '.preg_replace('/AND /', '', implode(' ', $sql), 1);
    }

    /**
     * Compile klausa ORDER BY.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    protected function orderings(Query $query)
    {
        foreach ($query->orderings as $ordering) {
            $sql[] = $this->wrap($ordering['column']).' '.strtoupper($ordering['direction']);
        }

        return 'ORDER BY '.implode(', ', $sql);
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
        return 'LIMIT '.$query->limit;
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
        return 'OFFSET '.$query->offset;
    }

    /**
     * Compile statemant INSERT dari instance kelas Query.
     *
     * @param \System\Database\Query $query
     * @param array                  $values
     *
     * @return string
     */
    public function insert(Query $query, $values)
    {
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));
        $parameters = $this->parameterize(reset($values));
        $parameters = implode(', ', array_fill(0, count($values), "($parameters)"));

        return "INSERT INTO {$table} ({$columns}) VALUES {$parameters}";
    }

    /**
     * Compile statemant INSERT dari instance kelas Query dan ambil ID-nya.
     *
     * @param \System\Database\Query $query
     * @param string                 $values
     * @param string                 $column
     *
     * @return string
     */
    public function insertGetId(Query $query, $values, $column)
    {
        return $this->insert($query, $values);
    }

    /**
     * Compile statemant UPDATE dari instance kelas Query.
     *
     * @param \System\Database\Query $query
     * @param array                  $values
     *
     * @return string
     */
    public function update(Query $query, $values)
    {
        $table = $this->wrapTable($query->from);

        foreach ($values as $column => $value) {
            $columns[] = $this->wrap($column).' = '.$this->parameter($value);
        }

        $columns = implode(', ', $columns);

        return trim("UPDATE {$table} SET {$columns} ".$this->wheres($query));
    }

    /**
     * Compile statemant DELETE dari instance kelas Query.
     *
     * @param \System\Database\Query $query
     *
     * @return string
     */
    public function delete(Query $query)
    {
        $table = $this->wrapTable($query->from);

        return trim("DELETE FROM {$table} ".$this->wheres($query));
    }

    /**
     * Ganti statement shortcut (binding value) di sql menjadi query yang sah untuk PDO.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return string
     */
    public function shortcut($sql, &$bindings)
    {
        if (false !== strpos($sql, '(...)')) {
            for ($i = 0; $i < count($bindings); $i++) {
                if (is_array($bindings[$i])) {
                    $parameters = $this->parameterize($bindings[$i]);
                    array_splice($bindings, $i, 1, $bindings[$i]);
                    $sql = preg_replace('~\(\.\.\.\)~', "({$parameters})", $sql, 1);
                }
            }
        }

        return trim($sql);
    }
}
