<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;
use System\Database\ORM\Query;

abstract class Relationship extends Query
{
    protected $base;

    /**
     * Buat instance relasi has-one/has-many baru.
     *
     * @param \System\Database\ORM\Model $model
     * @param string                     $associated
     * @param string                     $foreign
     */
    public function __construct($model, $associated, $foreign)
    {
        $this->foreign = $foreign;

        if ($associated instanceof Model) {
            $this->model = $associated;
        } else {
            $this->model = new $associated();
        }

        if ($model instanceof Model) {
            $this->base = $model;
        } else {
            $this->base = new $model();
        }

        $this->table = $this->table();
        $this->constrain();
    }

    /**
     * Ambil nama foreign key model.
     *
     * @param string $model
     * @param string $foreign
     *
     * @return string
     */
    public static function foreign($model, $foreign = null)
    {
        if (!is_null($foreign)) {
            return $foreign;
        }

        if (is_object($model)) {
            $model = class_basename($model);
        }

        return strtolower(basename($model).'_id');
    }

    /**
     * Ambil objek instance kelas model baru (singleton).
     *
     * @param array $attributes
     *
     * @return \System\Database\ORM\Model
     */
    protected function freshModel($attributes = [])
    {
        $attributes = (array) $attributes;
        $class = get_class($this->model);

        return new $class($attributes);
    }

    /**
     * Ambil foreign key untuk relasi.
     *
     * @return string
     */
    public function foreignKey()
    {
        return static::foreign($this->base, $this->foreign);
    }

    /**
     * Ambil semua primary key dari resultset.
     *
     * @param array $results
     *
     * @return array
     */
    public function keys($results)
    {
        $results = (array) $results;

        $keys = [];
        foreach ($results as $result) {
            $keys[] = $result->getKey();
        }

        return array_unique($keys);
    }

    /**
     * Ambil daftar relasi yang harus di-eagerload.
     *
     * @param array $eagerloads
     *
     * @return $this
     */
    public function with($eagerloads)
    {
        $eagerloads = (array) $eagerloads;
        $this->model->eagerloads = $eagerloads;

        return $this;
    }
}
