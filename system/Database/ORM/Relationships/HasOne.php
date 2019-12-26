<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

class HasOne extends HasOneOrMany
{
    /**
     * Ambil hasil dari query relasi model.
     *
     * @return \System\Database\ORM\Model
     */
    public function results()
    {
        return parent::first();
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
            $parent->relationships[$relationship] = null;
        }
    }

    /**
     * Pasangkan child model yang di-eagerload dengan parentnya.
     *
     * @param string $relationship
     * @param array  $parents
     * @param array  $children
     */
    public function match($relationship, &$parents, $children)
    {
        $foreign = $this->foreignKey();
        $children = (array) $children;

        $dictionary = [];
        foreach ($children as $child) {
            $dictionary[$child->$foreign] = $child;
        }

        $parents = (array) $parents;

        foreach ($parents as $parent) {
            if (array_key_exists($key = $parent->getKey(), $dictionary)) {
                $parent->relationships[$relationship] = $dictionary[$key];
            }
        }
    }
}
