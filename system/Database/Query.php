<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use DateTime;
use Exception;
use System\Support\Str;

class Query
{
    public $connection;
    public $grammar;
    public $selects;
    public $aggregate;
    public $distinct = false;
    public $from;
    public $joins;
    public $wheres;
    public $groupings;
    public $havings;
    public $orderings;
    public $limit;
    public $offset;
    public $bindings = [];

    /**
     * Buat instance query baru.
     *
     * @param \System\Database\Connection             $connection
     * @param \System\Database\Query\Grammars\Grammar $grammar
     * @param string                                  $table
     */
    public function __construct(Connection $connection, Query\Grammars\Grammar $grammar, $table)
    {
        if (0 !== strpos($table, '\\')) {
            $table = explode('\\', $table);
            $table = end($table);
        }

        $this->from = $table;
        $this->grammar = $grammar;
        $this->connection = $connection;
    }

    /**
     * Paksa query untuk mereturn result distinct.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Tambahkan array kolom ke klausa SELECT.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->selects = (array) $columns;

        return $this;
    }

    /**
     * Tambahkan klausa join ke query.
     *
     * @param string $table
     * @param string $column1
     * @param string $operator
     * @param string $column2
     * @param string $type
     *
     * @return $this
     */
    public function join($table, $column1, $operator = null, $column2 = null, $type = 'INNER')
    {
        if ($column1 instanceof Closure) {
            $this->joins[] = new Query\Join($type, $table);
            call_user_func($column1, end($this->joins));
        } else {
            $join = new Query\Join($type, $table);
            $join->on($column1, $operator, $column2);
            $this->joins[] = $join;
        }

        return $this;
    }

    /**
     * Tambahkan klausa left join ke query.
     *
     * @param string $table
     * @param string $column1
     * @param string $operator
     * @param string $column2
     *
     * @return $this
     */
    public function leftJoin($table, $column1, $operator = null, $column2 = null)
    {
        return $this->join($table, $column1, $operator, $column2, 'LEFT');
    }

    /**
     * Reset klausa where ke kondisi awal.
     */
    public function resetWhere()
    {
        list($this->wheres, $this->bindings) = [[], []];
    }

    /**
     * Tambahkan klausa where mentah ke query.
     *
     * @param string $where
     * @param array  $bindings
     * @param string $connector
     *
     * @return $this
     */
    public function rawWhere($where, $bindings = [], $connector = 'AND')
    {
        $this->wheres[] = [
            'type'      => 'whereRaw',
            'connector' => $connector,
            'sql'       => $where,
        ];

        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    /**
     * Tambahkan klausa or where mentah ke query.
     *
     * @param string $where
     * @param array  $bindings
     *
     * @return $this
     */
    public function rawOrWhere($where, $bindings = [])
    {
        return $this->rawWhere($where, $bindings, 'OR');
    }

    /**
     * Tambahkan klausa where ke query.
     *
     * @param string     $where
     * @param array      $bindings
     * @param string     $connector
     * @param mixed      $column
     * @param mixed|null $operator
     * @param mixed|null $value
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $connector = 'AND')
    {
        if ($column instanceof Closure) {
            return $this->whereNested($column, $connector);
        }

        $type = 'where';
        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'connector');
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Tambahkan klausa or where ke query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Tambahkan klausa or where primary key ke query.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function orWhereId($value)
    {
        return $this->orWhere('id', '=', $value);
    }

    /**
     * Tambahkan klausa where in ke query.
     *
     * @param string $column
     * @param array  $values
     * @param string $connector
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIn($column, $values, $connector = 'AND', $not = false)
    {
        $type = ($not) ? 'whereNotIn' : 'whereIn';
        $this->wheres[] = compact('type', 'column', 'values', 'connector');
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Tambahkan klausa or where in ke query.
     *
     * @param string $column
     * @param array  $values
     *
     * @return $this
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Tambahkan klausa where not in ke query.
     *
     * @param string $column
     * @param array  $values
     * @param string $connector
     *
     * @return $this
     */
    public function whereNotIn($column, $values, $connector = 'AND')
    {
        return $this->whereIn($column, $values, $connector, true);
    }

    /**
     * Tambahkan klausa or where not in ke query.
     *
     * @param string $column
     * @param array  $values
     *
     * @return $this
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'OR');
    }

    /**
     * Tambahkan klausa between ke query.
     *
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @param string $connector
     * @param bool   $not
     *
     * @return $this
     */
    public function whereBetween($column, $min, $max, $connector = 'AND', $not = false)
    {
        $type = ($not) ? 'whereNotBetween' : 'whereBetween';
        $this->wheres[] = compact('type', 'column', 'min', 'max', 'connector');
        $this->bindings[] = $min;
        $this->bindings[] = $max;

        return $this;
    }

    /**
     * Tambahkan klausa or between ke query.
     *
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     *
     * @return $this
     */
    public function orWhereBetween($column, $min, $max)
    {
        return $this->whereBetween($column, $min, $max, 'OR');
    }

    /**
     * Tambahkan klausa where not between ke query.
     *
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @param string $connector
     *
     * @return $this
     */
    public function whereNotBetween($column, $min, $max, $connector = 'AND')
    {
        return $this->whereBetween($column, $min, $max, $connector, true);
    }

    /**
     * Tambahkan klausa or where not between ke query.
     *
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     *
     * @return $this
     */
    public function orWhereNotBetween($column, $min, $max)
    {
        return $this->whereNotBetween($column, $min, $max, 'OR');
    }

    /**
     * Tambahkan klausa where is null ke query.
     *
     * @param string $column
     * @param string $connector
     * @param bool   $not
     *
     * @return $this
     */
    public function whereNull($column, $connector = 'AND', $not = false)
    {
        $type = ($not) ? 'whereNotNull' : 'whereNull';
        $this->wheres[] = compact('type', 'column', 'connector');

        return $this;
    }

    /**
     * Tambahkan klausa or where is null ke query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'OR');
    }

    /**
     * Tambahkan klausa where is not null ke query.
     *
     * @param string $column
     * @param string $connector
     *
     * @return $this
     */
    public function whereNotNull($column, $connector = 'AND')
    {
        return $this->whereNull($column, $connector, true);
    }

    /**
     * Tambahkan klausa or where is not null ke query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'OR');
    }

    /**
     * Tambahkan klausa where bersarang ke query.
     *
     * @param callable $callback
     * @param string   $connector
     *
     * @return $this
     */
    public function whereNested(callable $callback, $connector = 'AND')
    {
        $type = 'whereNested';
        $query = new self($this->connection, $this->grammar, $this->from);

        call_user_func($callback, $query);

        if (null !== $query->wheres) {
            $this->wheres[] = compact('type', 'query', 'connector');
        }

        $this->bindings = array_merge($this->bindings, $query->bindings);

        return $this;
    }

    /**
     * Tambahkan klausa where 'dinamis' ke query.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return $this
     */
    private function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);
        $segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);

        $connector = 'AND';
        $index = 0;

        foreach ($segments as $segment) {
            if ('And' != $segment && 'Or' != $segment) {
                $this->where(strtolower($segment), '=', $parameters[$index], $connector);
                $index++;
            } else {
                $connector = strtoupper($segment);
            }
        }

        return $this;
    }

    /**
     * Tambahkan klausa group by ke query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function groupBy($column)
    {
        $this->groupings[] = $column;

        return $this;
    }

    /**
     * Tambahkan klausa having ke query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return $this
     */
    public function having($column, $operator, $value)
    {
        $this->havings[] = compact('column', 'operator', 'value');
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Tambahkan klausa order by ke query.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->orderings[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Set query offset.
     *
     * @param int $value
     *
     * @return $this
     */
    public function skip($value)
    {
        $this->offset = $value;

        return $this;
    }

    /**
     * Set query limit.
     *
     * @param int $value
     *
     * @return $this
     */
    public function take($value)
    {
        $this->limit = $value;

        return $this;
    }

    /**
     * Set limit dan offset untuk halaman yang diminta (untuk paging).
     *
     * @param int $page
     * @param int $perpage
     *
     * @return $this
     */
    public function forPage($page, $perpage)
    {
        return $this->skip(($page - 1) * $perpage)->take($perpage);
    }

    /**
     * Cari record berdasarkan primary key.
     *
     * @param int   $id
     * @param array $columns
     *
     * @return object
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Jalankan query SELECT dan return satu kolom.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function only($column)
    {
        $sql = $this->grammar->select($this->select([$column]));

        return $this->connection->only($sql, $this->bindings);
    }

    /**
     * Jalankan query SELECT dan return hasil pertama.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $columns = (array) $columns;
        $results = $this->take(1)->get($columns);

        return (count($results) > 0) ? $results[0] : null;
    }

    /**
     * Ambil array dengan nilai kolom yang diberikan.
     *
     * @param string $column
     * @param string $key
     *
     * @return array
     */
    public function lists($column, $key = null)
    {
        $columns = (is_null($key)) ? [$column] : [$column, $key];
        $results = $this->get($columns);
        $values = array_map(function ($row) use ($column) {
            return $row->{$column};
        }, $results);

        if (!is_null($key) && count($results)) {
            return array_combine(array_map(function ($row) use ($key) {
                return $row->{$key};
            }, $results), $values);
        }

        return $values;
    }

    /**
     * Jalankan query SELECT dan return seluruh hasilnya.
     *
     * @param array $columns
     *
     * @return array
     */
    public function get($columns = ['*'])
    {
        if (is_null($this->selects)) {
            $this->select($columns);
        }

        $sql = $this->grammar->select($this);
        $results = $this->connection->query($sql, $this->bindings);

        if ($this->offset > 0 && $this->grammar instanceof Query\Grammars\SQLServer) {
            array_walk($results, function ($result) {
                unset($result->rownum);
            });
        }

        $this->selects = null;

        return $results;
    }

    /**
     * Ambil nilai aggregate.
     *
     * @param string $aggregator
     * @param array  $columns
     *
     * @return mixed
     */
    public function aggregate($aggregator, $columns)
    {
        $this->aggregate = compact('aggregator', 'columns');
        $sql = $this->grammar->select($this);
        $result = $this->connection->only($sql, $this->bindings);
        $this->aggregate = null;

        return $result;
    }

    /**
     * Ambil hasil queri paginasi sebagai instance dari kelas Paginator.
     *
     * @param int   $perpage
     * @param array $columns
     *
     * @return \System\Database\Paginator
     */
    public function paginate($perpage = 20, $columns = ['*'])
    {
        list($orderings, $this->orderings) = [$this->orderings, null];
        $total = $this->count(reset($columns));
        $page = Paginator::page($total, $perpage);

        $this->orderings = $orderings;
        $results = $this->forPage($page, $perpage)->get($columns);

        return Paginator::make($results, $total, $perpage);
    }

    /**
     * Insert data ke tabel.
     *
     * @param array $values
     *
     * @return bool
     */
    public function insert($values)
    {
        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $bindings = [];

        foreach ($values as $value) {
            $bindings = array_merge($bindings, array_values($value));
        }

        $sql = $this->grammar->insert($this, $values);

        return $this->connection->query($sql, $bindings);
    }

    /**
     * Insert data ke tabel dan return primary id nya.
     *
     * @param array  $values
     * @param string $values
     * @param mixed  $column
     *
     * @return mixed
     */
    public function insertGetId($values, $column = 'id')
    {
        if (!isset($values['created_at'])) {
            $values['created_at'] = new DateTime();
        }

        $sql = $this->grammar->insertGetId($this, $values, $column);
        $result = $this->connection->query($sql, array_values($values));

        if (isset($values[$column])) {
            return $values[$column];
        } elseif ($this->grammar instanceof Query\Grammars\Postgres) {
            $row = (array) $result[0];

            return (int) $row[$column];
        }

        return (int) $this->connection->pdo->lastInsertId();
    }

    /**
     * Naikkan (tambah) nilai kolom dengan jumlah yang diberikan.
     *
     * @param string $column
     * @param int    $amount
     *
     * @return int
     */
    public function increment($column, $amount = 1)
    {
        return $this->adjust($column, $amount, ' + ');
    }

    /**
     * Turunkan (kurangi) nilai kolom dengan jumlah yang diberikan.
     *
     * @param string $column
     * @param int    $amount
     *
     * @return int
     */
    public function decrement($column, $amount = 1)
    {
        return $this->adjust($column, $amount, ' - ');
    }

    /**
     * Sesuaikan nilai kolom ke atas atau ke bawah sesuai jumlah yang diberikan.
     *
     * @param string $column
     * @param int    $amount
     * @param string $operator
     *
     * @return int
     */
    protected function adjust($column, $amount, $operator)
    {
        $wrapped = $this->grammar->wrap($column);
        $value = Database::raw($wrapped.$operator.$amount);

        return $this->update([$column => $value]);
    }

    /**
     * Update data di database.
     *
     * @param array $values
     *
     * @return int
     */
    public function update($values)
    {
        $bindings = array_merge(array_values($values), $this->bindings);
        $sql = $this->grammar->update($this, $values);

        return $this->connection->query($sql, $bindings);
    }

    /**
     * Eksekusi query DELETE.
     *
     * @param int $id
     *
     * @return int
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where('id', '=', $id);
        }

        $sql = $this->grammar->delete($this);

        return $this->connection->query($sql, $this->bindings);
    }

    /**
     * Magic method untuk handle pemanggilan method secara dinamis.
     * Method ini meng-handle pemanggilan method - method aggregate dan where.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $orig = $method;
        $method = Str::camel($method);

        if (0 === strpos($method, 'where')) {
            return $this->dynamicWhere($method, $parameters, $this);
        }

        if (in_array($method, ['count', 'min', 'max', 'avg', 'sum'])) {
            if (0 === count($parameters)) {
                $parameters[0] = '*';
            }

            return $this->aggregate(strtoupper($method), (array) $parameters[0]);
        }

        throw new Exception("Method [$orig] is not defined on the Query class.");
    }
}
