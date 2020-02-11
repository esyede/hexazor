<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

use System\Database\ORM\Model;

class BelongsTo extends Relationship
{
    /**
     * Ambil hasil pertama dari query relasi model.
     *
     * @return \System\Database\ORM\Model
     */
    public function results()
    {
        return parent::first();
    }

    /**
     * Update parent model relasi.
     *
     * @param array|\System\Database\ORM\Model $attributes
     *
     * @return int
     */
    public function update($attributes)
    {
        $attributes = ($attributes instanceof Model) ? $attributes->getDirty() : $attributes;

        return $this->model->update($this->foreignValue(), $attributes);
    }

    /**
     * Set constrain pada tabel relasi.
     */
    protected function constrain()
    {
        $this->table->where($this->model->key(), '=', $this->foreignValue());
    }

    /**
     * Inisialisasi pada beberapa parent model.
     *
     * @param array  $parents
     * @param string $relationship
     */
    public function initialize(&$parents, $relationship)
    {
        $parents = (array) $parents;

        foreach ($parents as &$parent) {
            $parent->relationships[$relationship] = null;
        }
    }

    /**
     * Set constrain pada tabel relasi yang di-eagerload.
     *
     * @param array $results
     */
    public function eagerlyConstrain($results)
    {
        $results = (array) $results;
        $keys = [];

        foreach ($results as $result) {
            if (!is_null($key = $result->{$this->foreignKey()})) {
                $keys[] = $key;
            }
        }

        if (0 === count($keys)) {
            $keys = [0];
        }

        $this->table->whereIn($this->model->key(), array_unique($keys));
    }

    /**
     * Pasangkan child model yang di-eagerload dengan parentnya.
     *
     * @param string $relationship
     * @param array  $children
     * @param array  $parents
     */
    public function match($relationship, &$children, $parents)
    {
        $parents = (array) $parents;
        $foreign = $this->foreignKey();
        $dictionary = [];
        
        foreach ($parents as $parent) {
            $dictionary[$parent->getKey()] = $parent;
        }

        $children = (array) $children;

        foreach ($children as $child) {
            if (array_key_exists($child->{$foreign}, $dictionary)) {
                $child->relationships[$relationship] = $dictionary[$child->{$foreign}];
            }
        }
    }

    /**
     * Ambil foreign-key dari base model.
     *
     * @return mixed
     */
    public function foreignValue()
    {
        return $this->base->getAttribute($this->foreign);
    }

    /**
     * Bind objek ke relasi belongs-to berdasarkan id-nya.
     *
     * @param string $id
     *
     * @return object
     */
    public function bind($id)
    {
        $this->base->fill([$this->foreign => $id])->save();

        return $this->base;
    }
}
