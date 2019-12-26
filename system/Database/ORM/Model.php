<?php

namespace System\Database\ORM;

defined('DS') or exit('No direct script access allowed.');

use DateTime;
use System\Database\Database;
use System\Support\Str;

abstract class Model
{
    public $attributes = [];
    public $original = [];
    public $relationships = [];
    public $eagerloads = [];
    public $exists = false;

    public static $key = 'id';
    public static $fillable;
    public static $hidden = [];
    public static $timestamps = true;
    public static $table;
    public static $connection;
    public static $sequence;
    public static $perpage = 20;

    /**
     * Buat instance kelas Model baru.
     *
     * @param array $attributes
     * @param bool  $exists
     */
    public function __construct($attributes = [], $exists = false)
    {
        $attributes = (array) $attributes;
        $this->exists = $exists;
        $this->fill($attributes);
    }

    /**
     * Isi model dengan array attributenya.
     *
     * @param array $attributes
     * @param bool  $raw
     */
    public function fill(array $attributes, $raw = false)
    {
        foreach ($attributes as $key => $value) {
            if ($raw) {
                $this->setAttribute($key, $value);
                continue;
            }

            if (is_array(static::$fillable)) {
                if (in_array($key, static::$fillable)) {
                    $this->$key = $value;
                }
            } else {
                $this->$key = $value;
            }
        }

        if (0 === count($this->original)) {
            $this->original = $this->attributes;
        }

        return $this;
    }

    /**
     * Isi model dengan array.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function fillRaw(array $attributes)
    {
        return $this->fill($attributes, true);
    }

    /**
     * Set attribute apa saja yang boleh diisi saat mass-assignment ke model.
     *
     * @param array $attributes
     */
    public static function fillable($attributes = null)
    {
        if (is_null($attributes)) {
            return static::$fillable;
        }

        $attributes = (array) $attributes;
        static::$fillable = $attributes;
    }

    /**
     * Buat instance model baru dan simpan ke database.
     *
     * @param array $attributes
     *
     * @return $this|false
     */
    public static function create($attributes)
    {
        $attributes = (array) $attributes;
        $model = new static($attributes);
        $success = $model->save();

        return ($success) ? $model : false;
    }

    /**
     * Update instance model di database.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return int
     */
    public static function update($id, $attributes)
    {
        $attributes = (array) $attributes;
        $model = new static([], true);
        $model->fill($attributes);

        if (static::$timestamps) {
            $model->timestamp();
        }

        return $model->query()
            ->where($model->key(), '=', $id)
            ->update($model->attributes);
    }

    /**
     * Ambil semua model di database.
     *
     * @return array
     */
    public static function all()
    {
        $static = new static();

        return $static->query()->get();
    }

    /**
     * Relasi yang harus di-eagerload oleh query.
     *
     * @param array $eagerloads
     *
     * @return $this
     */
    public function _with($eagerloads)
    {
        $eagerloads = (array) $eagerloads;
        $this->eagerloads = $eagerloads;

        return $this;
    }

    /**
     * Ambil query untuk relasi one-to-one.
     *
     * @param string $model
     * @param string $foreign
     *
     * @return \System\Database\ORM\Relationships\Relationship
     */
    public function hasOne($model, $foreign = null)
    {
        return new Relationships\HasOne($this, $model, $foreign);
    }

    /**
     * Ambil query untuk relasi one-to-many.
     *
     * @param string $model
     * @param string $foreign
     *
     * @return \System\Database\ORM\Relationships\Relationship
     */
    public function hasMany($model, $foreign = null)
    {
        return new Relationships\HasMany($this, $model, $foreign);
    }

    /**
     * Ambil query untuk relasi one-to-one (inverse dari hasOne).
     *
     * @param string $model
     * @param string $foreign
     *
     * @return \System\Database\ORM\Relationships\Relationship
     */
    public function belongsTo($model, $foreign = null)
    {
        if (is_null($foreign)) {
            list(, $caller) = debug_backtrace(false);
            $foreign = "{$caller['function']}_id";
        }

        return new Relationships\BelongsTo($this, $model, $foreign);
    }

    /**
     * Ambil query untuk relasi many-to-many.
     *
     * @param string $model
     * @param string $table
     * @param string $foreign
     * @param string $other
     *
     * @return \System\Database\ORM\Relationships\HasManyAndBelongsTo
     */
    public function hasManyAndBelongsTo($model, $table = null, $foreign = null, $other = null)
    {
        return new Relationships\HasManyAndBelongsTo($this, $model, $table, $foreign, $other);
    }

    /**
     * Sipan model dan seluruh relasinya ke database.
     *
     * @return bool
     */
    public function push()
    {
        $this->save();

        foreach ($this->relationships as $name => $models) {
            if (!is_array($models)) {
                $models = [$models];
            }

            foreach ($models as $model) {
                $model->push();
            }
        }
    }

    /**
     * Simpan instance model ke database.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->dirty()) {
            return true;
        }

        if (static::$timestamps) {
            $this->timestamp();
        }

        if ($this->exists) {
            $query = $this->query()->where(static::$key, '=', $this->getKey());
            $result = 1 === $query->update($this->getDirty());
        } else {
            $id = $this->query()->insertGetId($this->attributes, $this->key());
            $this->setKey($id);
            $this->exists = $result = is_numeric($this->getKey());
        }

        $this->original = $this->attributes;

        return $result;
    }

    /**
     * Hapus model dari database.
     *
     * @return int
     */
    public function delete()
    {
        if ($this->exists) {
            $result = $this->query()->where(static::$key, '=', $this->getKey())->delete();

            return $result;
        }
    }

    /**
     * Set timestamp model (created_at, updated_at).
     */
    public function timestamp()
    {
        $this->updated_at = new DateTime();

        if (!$this->exists) {
            $this->created_at = $this->updated_at;
        }
    }

    /**
     * Update (hanya) timestamp model dan langsung simpan.
     */
    public function touch()
    {
        $this->timestamp();
        $this->save();
    }

    /**
     * Buat instance qyery builder baru.
     *
     * @return \System\Database\ORM\Query
     */
    protected function _query()
    {
        return new Query($this);
    }

    /**
     * Sinkronkan attribute asli (bawaan) model dengan attribute saat ini.
     *
     * @return bool
     */
    final public function sync()
    {
        $this->original = $this->attributes;

        return true;
    }

    /**
     * Cek apakah attribute yang diberikan telah berubah dari keadaan semula.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function changed($attribute)
    {
        return array_get($this->attributes, $attribute) != array_get($this->original, $attribute);
    }

    /**
     * Cek apakah attribute sudah diubah dari keadaan semula
     * Model yang belum disimpan ke database akan selalu dianggap 'dirty'.
     *
     * @return bool
     */
    public function dirty()
    {
        return !$this->exists || count($this->getDirty()) > 0;
    }

    /**
     * Ambil nama tabel yang terkait dengan model.
     *
     * @return string
     */
    public function table()
    {
        $table = is_object($this) ? get_class($this) : basename(str_replace('\\', '/', $this));

        return static::$table ?: strtolower(Str::plural($table));
    }

    /**
     * Ambil attribute 'dirty' untuk model.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original)
            || $value != $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Ambil nama primary key model.
     *
     * @return string
     */
    public function getKey()
    {
        return array_get($this->attributes, static::$key);
    }

    /**
     * Set nama primary key model.
     *
     * @param string $value
     */
    public function setKey($value)
    {
        return $this->setAttribute(static::$key, $value);
    }

    /**
     * Ambil data attribute dari model.
     *
     * @param string $key
     *
     * @return string
     */
    public function getAttribute($key)
    {
        return array_get($this->attributes, $key);
    }

    /**
     * Set data attribute model.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        $key = ltrim(Str::snake($key), '_');
        $this->attributes[$key] = $value;
    }

    /**
     * Buang attribute dari model.
     *
     * @param string $key
     */
    final public function purge($key)
    {
        unset($this->original[$key], $this->attributes[$key]);
    }

    /**
     * Ambil data attribute dan relasi model dalam bentuk array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = [];
        $attrKeys = array_keys($this->attributes);
        foreach ($attrKeys as $attribute) {
            if (!in_array($attribute, static::$hidden)) {
                $attributes[$attribute] = $this->$attribute;
            }
        }

        foreach ($this->relationships as $name => $models) {
            if (in_array($name, static::$hidden)) {
                continue;
            }

            if ($models instanceof self) {
                $attributes[$name] = $models->toArray();
            } elseif (is_array($models)) {
                $attributes[$name] = [];
                foreach ($models as $id => $model) {
                    $attributes[$name][$id] = $model->toArray();
                }
            } elseif (is_null($models)) {
                $attributes[$name] = $models;
            }
        }

        return $attributes;
    }

    /**
     * Magic getter.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->relationships)) {
            return $this->relationships[$key];
        } elseif (array_key_exists($key, $this->attributes)) {
            $key = Str::studly($key);

            return $this->{"get{$key}"}();
        } elseif (method_exists($this, $key)) {
            return $this->relationships[$key] = $this->$key()->results();
        }

        $key = Str::studly($key);

        return $this->{"get{$key}"}();
    }

    /**
     * Magic setter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $key = Str::studly($key);
        $this->{"set{$key}"}($value);
    }

    /**
     * Cek adakah attribute ada pada model.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        $sources = ['attributes', 'relationships'];

        foreach ($sources as $source) {
            if (array_key_exists($key, $this->{$source})) {
                return !empty($this->{$source}[$key]);
            }
        }

        return false;
    }

    /**
     * Hapus attribute dari model.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relationships[$key]);
    }

    /**
     * Tangani method call dinamis pada model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $meta = ['key', 'table', 'connection', 'sequence', 'perpage', 'timestamps'];

        if (in_array($method, $meta)) {
            return static::$$method;
        }

        $underscored = ['with', 'query']; // _with(), _query()

        if (in_array($method, $underscored)) {
            return call_user_func_array([$this, '_'.$method], $parameters);
        }

        if (0 === strpos($method, 'get')) {
            return $this->getAttribute(Str::snake(substr($method, 3)));
        } elseif (0 === strpos($method, 'set')) {
            $this->setAttribute(Str::snake(substr($method, 3)), $parameters[0]);
        } else {
            return call_user_func_array([$this->query(), $method], $parameters);
        }
    }

    /**
     * Tangani method call statis pada model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $model = get_called_class();

        return call_user_func_array([new $model(), $method], $parameters);
    }
}
