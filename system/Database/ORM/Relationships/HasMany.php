<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

class HasMany extends HasOneOrMany
{
    /**
     * Ambil hasil dari query relasi model.
     *
     * @return \System\Database\ORM\Model
     */
    public function results()
    {
        return parent::get();
    }

    /**
     * Sinkronkan tabel relasi dengan modelnya.
     *
     * @param mixed $models
     *
     * @return bool
     */
    public function save($models)
    {
        $current = $this->table->lists($this->model->key());

        if (!is_array($models)) {
            $models = [$models];
        }

        foreach ($models as $attributes) {
            $class = get_class($this->model);
            
            if ($attributes instanceof $class) {
                $model = $attributes;
            } else {
                $model = $this->freshModel($attributes);
            }

            $foreign = $this->foreignKey();
            $model->{$foreign} = $this->base->getKey();
            $id = $model->getKey();

            $model->exists = (!is_null($id) && in_array($id, $current));
            $model->original = [];
            $model->save();
        }

        return true;
    }

    /**
     * Inisialisasi relasi pada beberapa parent model.
     *
     * @param array  $parents
     * @param string $relationship
     */
    public function initialize(&$parents, $relationship)
    {
        $parents = (array) $parents;

        foreach ($parents as &$parent) {
            $parent->relationships[$relationship] = [];
        }
    }

    /**
     * Pasangkan child model yang di-eagerload dengan parentnya.
     *
     * @param array $parents
     * @param array $children
     * @param mixed $relationship
     */
    public function match($relationship, &$parents, $children)
    {
        $children = (array) $children;
        $foreign = $this->foreignKey();

        $dictionary = [];
        
        foreach ($children as $child) {
            $dictionary[$child->{$foreign}][] = $child;
        }

        $parents = (array) $parents;

        foreach ($parents as $parent) {
            if (array_key_exists($key = $parent->getKey(), $dictionary)) {
                $parent->relationships[$relationship] = $dictionary[$key];
            }
        }
    }
}
