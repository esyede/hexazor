<?php

namespace System\Database\ORM;

defined('DS') or exit('No direct script access allowed.');

use System\Database\Database;
use System\Support\Str;

class Query
{
    public $model;
    public $table;
    public $eagerloads = [];
    public $passthru = [
        'lists', 'only', 'insert', 'insertGetId',
        'update', 'increment', 'delete', 'decrement',
        'count', 'min', 'max', 'avg', 'sum',
    ];

    /**
     * Buat instance query baru untuk model.
     *
     * @param \System\Database\ORM\Model $model
     */
    public function __construct($model)
    {
        $this->model = ($model instanceof Model) ? $model : new $model();
        $this->table = $this->table();
    }

    /**
     * Cari model berdasarkan primary-keynya.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $model = $this->model;
        $this->table->where($model::$key, '=', $id);

        return $this->first($columns);
    }

    /**
     * Ambil hasil pertama dari query.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->hydrate($this->model, $this->table->take(1)->get($columns));

        return (count($results) > 0) ? reset($results) : null;
    }

    /**
     * Ambil semua hasil query.
     *
     * @param array $columns
     *
     * @return array
     */
    public function get($columns = ['*'])
    {
        return $this->hydrate($this->model, $this->table->get($columns));
    }

    /**
     * Ambil array hasil query untuk paging.
     *
     * @param int   $perpage
     * @param array $columns
     *
     * @return \System\Database\Paginator
     */
    public function paginate($perpage = null, $columns = ['*'])
    {
        $perpage = $perpage ?: $this->model->perpage();
        $paginator = $this->table->paginate($perpage, $columns);
        $paginator->results = $this->hydrate($this->model, $paginator->results);

        return $paginator;
    }

    /**
     * Isi array model dari hasil query yang diberikan.
     *
     * @param \System\Database\ORM\Model $model
     * @param array                      $results
     *
     * @return array
     */
    public function hydrate($model, $results)
    {
        $results = (array) $results;
        $class = get_class($model);

        $models = [];

        foreach ($results as $result) {
            $result = (array) $result;
            $new = new $class([], true);
            $new->fillRaw($result);
            $models[] = $new;
        }

        if (count($results) > 0) {
            $eagerloadedModels = $this->getEagerloadedModels();
            foreach ($eagerloadedModels as $relationship => $constraints) {
                if (Str::contains($relationship, '.')) {
                    continue;
                }

                $this->load($models, $relationship, $constraints);
            }
        }

        if ($this instanceof Relationships\HasManyAndBelongsTo) {
            $this->hydratePivot($models);
        }

        return $models;
    }

    /**
     * Isi relasi yang di-eagerload pada result table.
     *
     * @param array      $results
     * @param string     $relationship
     * @param array|null $constraints
     */
    protected function load(&$results, $relationship, $constraints)
    {
        $query = $this->model->{$relationship}();
        $query->model->eagerloads = $this->nestedEagerloads($relationship);
        $query->table->resetWhere();
        $query->eagerlyConstrain($results);

        if (!is_null($constraints)) {
            $query->table->whereNested($constraints);
        }

        $results = (array) $results;

        $query->initialize($results, $relationship);
        $query->match($relationship, $results, $query->get());
    }

    /**
     * Kumpulkan nested include untuk relasi yang diberikan.
     *
     * @param string $relationship
     *
     * @return array
     */
    protected function nestedEagerloads($relationship)
    {
        $eagerloadedModels = $this->getEagerloadedModels();

        $nested = [];
        foreach ($eagerloadedModels as $include => $constraints) {
            if (Str::startsWith($include, $relationship.'.')) {
                $nested[substr($include, strlen($relationship.'.'))] = $constraints;
            }
        }

        return $nested;
    }

    /**
     * Ambil relasi yang di-eagerload pada model.
     *
     * @return array
     */
    protected function getEagerloadedModels()
    {
        $eagerloads = [];
        foreach ($this->model->eagerloads as $relationship => $constraints) {
            if (is_numeric($relationship)) {
                list($relationship, $constraints) = [$constraints, null];
            }

            $eagerloads[$relationship] = $constraints;
        }

        return $eagerloads;
    }

    /**
     * Ambil query builder untuk model.
     *
     * @return \System\Database\ORM\Query
     */
    protected function table()
    {
        return $this->connection()->table($this->model->table());
    }

    /**
     * Ambil koneksi database untuk model.
     *
     * @return \System\Database\Connection
     */
    public function connection()
    {
        return Database::connection($this->model->connection());
    }

    /**
     * Magic method call.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array([$this->table, $method], $parameters);
        if (in_array($method, $this->passthru)) {
            return $result;
        }

        return $this;
    }
}
