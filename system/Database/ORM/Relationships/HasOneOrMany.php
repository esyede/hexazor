<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

use DateTime;
use System\Database\ORM\Model;

class HasOneOrMany extends Relationship
{
    /**
     * Tambahkan record baru untuk relasi.
     *
     * @param array|\System\Database\ORM\Model $attributes
     *
     * @return \System\Database\ORM\Model|false
     */
    public function insert($attributes)
    {
        if ($attributes instanceof Model) {
            $attributes->setAttribute($this->foreignKey(), $this->base->getKey());

            return $attributes->save() ? $attributes : false;
        }

        $attributes[$this->foreignKey()] = $this->base->getKey();

        return $this->model->create($attributes);
    }

    /**
     * Update record di relasi.
     *
     * @param array $attributes
     *
     * @return bool
     */
    public function update(array $attributes)
    {
        if ($this->model->timestamps()) {
            $attributes['updated_at'] = new DateTime();
        }

        return $this->table->update($attributes);
    }

    /**
     * Set constrain untuk tabel relasi.
     */
    protected function constrain()
    {
        $this->table->where($this->foreignKey(), '=', $this->base->getKey());
    }

    /**
     * Set constrain tabel relasi untuk eagerload.
     *
     * @param array $results
     */
    public function eagerlyConstrain($results)
    {
        $this->table->whereIn($this->foreignKey(), $this->keys($results));
    }
}
