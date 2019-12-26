<?php

namespace System\Database\ORM\Relationships;

defined('DS') or exit('No direct script access allowed.');

use DateTime;
use System\Database\ORM\Model;
use System\Database\ORM\Pivot;

class HasManyAndBelongsTo extends Relationship
{
    protected $joining;
    protected $other;
    protected $with = ['id'];

    /**
     * Buat instance relasi many-to-many baru.
     *
     * @param \System\Database\ORM\Model $model
     * @param string                     $associated
     * @param string                     $table
     * @param string                     $foreign
     * @param void                       $other
     */
    public function __construct($model, $associated, $table, $foreign, $other)
    {
        $this->other = $other;
        $this->joining = $table ?: $this->joining($model, $associated);

        if (Pivot::$timestamps) {
            $this->with[] = 'created_at';
            $this->with[] = 'updated_at';
        }

        parent::__construct($model, $associated, $foreign);
    }

    /**
     * Tentukan nama tabel yang akan di-join untuk relasi.
     * Secara default, nama tabel relasinya akan di-join menggunakan underscore.
     *
     * @param \System\Database\ORM\Model $model
     * @param \System\Database\ORM\Model $associated
     *
     * @return string
     */
    protected function joining($model, $associated)
    {
        $models = [class_basename($model), class_basename($associated)];
        sort($models);

        // nama tabel relasi di-join menggunakan underscore
        return strtolower($models[0].'_'.$models[1]);
    }

    /**
     * Ambil isi relasi.
     *
     * @return array
     */
    public function results()
    {
        return parent::get();
    }

    /**
     * Tambahkan record baru kedalam tabel yang direlasikan.
     *
     * @param int|\System\Database\ORM\Model $id
     * @param array                          $attributes
     *
     * @return bool
     */
    public function attach($id, $attributes = [])
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $attributes = (array) $attributes;

        $joining = array_merge($this->joinRecord($id), $attributes);

        return $this->insertJoining($joining);
    }

    /**
     * Buang record dari tabel yang direlasikan.
     *
     * @param array|int|\System\Database\ORM\Model $ids
     *
     * @return bool
     */
    public function detach($ids)
    {
        if ($ids instanceof Model) {
            $ids = [$ids->getKey()];
        } elseif (!is_array($ids)) {
            $ids = [$ids];
        }

        return $this->pivot()->whereIn($this->otherKey(), $ids)->delete();
    }

    /**
     * Sinkronkan tabel yang di-join dengan ID yang diberikan.
     *
     * @param array $ids
     *
     * @return bool
     */
    public function sync($ids)
    {
        $ids = (array) $ids;
        $current = $this->pivot()->lists($this->otherKey());

        foreach ($ids as $id) {
            if (!in_array($id, $current)) {
                $this->attach($id);
            }
        }

        $detach = array_diff($current, $ids);

        if (count($detach) > 0) {
            $this->detach($detach);
        }
    }

    /**
     * Tambahkan record baru untuk relasi.
     *
     * @param array|\System\Database\ORM\Model $attributes
     * @param array                            $joining
     *
     * @return bool
     */
    public function insert($attributes, $joining = [])
    {
        if ($attributes instanceof Model) {
            $attributes = $attributes->attributes;
        }

        $model = $this->model->create($attributes);

        if ($model instanceof Model) {
            $joining = array_merge($this->joinRecord($model->getKey()), $joining);
            $result = $this->insertJoining($joining);
        }

        return ($model instanceof Model) && $result;
    }

    /**
     * Hapus semua record dari tabel yang di-join.
     *
     * @return int
     */
    public function delete()
    {
        return $this->pivot()->delete();
    }

    /**
     * Buat array yang mewakili record join baru untuk relasi.
     *
     * @param int $id
     *
     * @return array
     */
    protected function joinRecord($id)
    {
        return [
            $this->foreignKey() => $this->base->getKey(),
            $this->otherKey()   => $id,
        ];
    }

    /**
     * Tambahkan record baru untuk tabel yang di-join.
     *
     * @param array $attributes
     */
    protected function insertJoining($attributes)
    {
        if (Pivot::$timestamps) {
            $attributes['created_at'] = new DateTime();
            $attributes['updated_at'] = $attributes['created_at'];
        }

        return $this->joiningTable()->insert($attributes);
    }

    /**
     * Ambil query builder untuk tabel relasi yang di-join.
     *
     * @return \System\Database\ORM\Query
     */
    protected function joiningTable()
    {
        return $this->connection()->table($this->joining);
    }

    /**
     * Set constrain untuk tabel relasi.
     */
    protected function constrain()
    {
        $other = $this->otherKey();
        $foreign = $this->foreignKey();
        $this->setSelect($foreign, $other)->setJoin($other)->setWhere($foreign);
    }

    /**
     * Set klausa SELECT ke query builder untuk relasi.
     *
     * @param string $foreign
     * @param string $other
     */
    protected function setSelect($foreign, $other)
    {
        $columns = [$this->model->table().'.*'];
        $this->with = array_merge($this->with, [$foreign, $other]);

        foreach ($this->with as $column) {
            $columns[] = $this->joining.'.'.$column.' as pivot_'.$column;
        }

        $this->table->select($columns);

        return $this;
    }

    /**
     * Set klausa JOIN ke query builder untuk relasi.
     *
     * @param string $other
     */
    protected function setJoin($other)
    {
        $this->table->join($this->joining, $this->associatedKey(), '=', $this->joining.'.'.$other);

        return $this;
    }

    /**
     * Set klausa WHERE ke query builder untuk relasi.
     *
     * @param string $foreign
     */
    protected function setWhere($foreign)
    {
        $this->table->where($this->joining.'.'.$foreign, '=', $this->base->getKey());

        return $this;
    }

    /**
     * Inisialisasi relasi pada parent model.
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
     * Set constrain tabel relasi untuk eagerload.
     *
     * @param array $results
     */
    public function eagerlyConstrain($results)
    {
        $results = (array) $results;
        $this->table->whereIn($this->joining.'.'.$this->foreignKey(), $this->keys($results));
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
            $dictionary[$child->pivot->$foreign][] = $child;
        }

        $parents = (array) $parents;

        foreach ($parents as $parent) {
            if (array_key_exists($key = $parent->getKey(), $dictionary)) {
                $parent->relationships[$relationship] = $dictionary[$key];
            }
        }
    }

    /**
     * Isi model Pivot model ke array hasil query.
     *
     * @param array $results
     */
    protected function hydratePivot(&$results)
    {
        $results = (array) $results;

        foreach ($results as &$result) {
            $pivot = new Pivot($this->joining, $this->model->connection());
            foreach ($result->attributes as $key => $value) {
                if (0 === strpos($key, 'pivot_')) {
                    $pivot->{substr($key, 6)} = $value;
                    $result->purge($key);
                }
            }

            $result->relationships['pivot'] = $pivot;
            $pivot->sync() && $result->sync();
        }
    }

    /**
     * Set kolom mana yang harus diambil pada tabel yang di-join.
     *
     * @param array $columns
     *
     * @return \System\Database\ORM\Relationships\Relationship
     */
    public function with($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->with = array_unique(array_merge($this->with, $columns));
        $this->setSelect($this->foreignKey(), $this->otherKey());

        return $this;
    }

    /**
     * Ambil instance relasi dari tabel pivot.
     *
     * @return \System\Database\ORM\Relationships\HasMany
     */
    public function pivot()
    {
        $pivot = new Pivot($this->joining, $this->model->connection());

        return new HasMany($this->base, $pivot, $this->foreignKey());
    }

    /**
     * Ambil foreign key yang ber-asosiasi untuk relasi.
     *
     * @return string
     */
    protected function otherKey()
    {
        return Relationship::foreign($this->model, $this->other);
    }

    /**
     * Ambil key tabel yang ber-asosiasi.
     *
     * @return string
     */
    protected function associatedKey()
    {
        return $this->model->table().'.'.$this->model->key();
    }
}
