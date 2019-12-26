<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

use ArrayAccess;
use ArrayIterator;
use CachingIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected $items = [];

    /**
     * Buat instance collection baru.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Buat instance collection baru jika belum ada.
     *
     * @param mixed $items
     */
    public static function make($items)
    {
        if (is_null($items)) {
            return new static();
        }

        if ($items instanceof self) {
            return $items;
        }

        return new static(is_array($items) ? $items : [$items]);
    }

    /**
     * Ambil semua item dari collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Collapse collectioon menjadi satu array.
     *
     * @return static
     */
    public function collapse()
    {
        $results = [];

        foreach ($this->items as $values) {
            if ($values instanceof self) {
                $values = $values->all();
            }

            $results = array_merge($results, $values);
        }

        return new static($results);
    }

    /**
     * Cek apakah item ada di collection.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        if ($value instanceof Closure) {
            return !is_null($this->first($value));
        }

        return in_array($value, $this->items);
    }

    /**
     * Diff collection berdasarkan item yang diberikan.
     *
     * @param \System\Support\Collection|array $items
     *
     * @return array
     */
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Jalankan callback pada tiap - tiap item.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function each(Closure $callback)
    {
        array_map($callback, $this->items);

        return $this;
    }

    /**
     * Ambil elemen bersarang dari collection.
     *
     * @param string $key
     *
     * @return static
     */
    public function fetch($key)
    {
        return new static(Arr::fetch($this->items, $key));
    }

    /**
     * Jalankan filter untuk tiap - tiap item.
     *
     * @param \Closure $callback
     *
     * @return static
     */
    public function filter(Closure $callback)
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * Ambil item pertama dari collection.
     *
     * @param \Closure $callback
     * @param mixed    $default
     *
     * @return mixed|null
     */
    public function first(Closure $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return count($this->items) > 0 ? reset($this->items) : null;
        }

        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Ambil array item yang telah di-rata-kan dalam collection.
     *
     * @return static
     */
    public function flatten()
    {
        return new static(Arr::flatten($this->items));
    }

    /**
     * Balik item - item di collection.
     *
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * Hapus item dari collection berdasarkan key.
     *
     * @param mixed $key
     */
    public function forget($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Ambi item dari collection berdasarkan key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return value($default);
    }

    /**
     * Kelompokkan sebuah array asosiatifberdasarkan nilai field atau Closure.
     *
     * @param callable|string $groupBy
     *
     * @return static
     */
    public function groupBy($groupBy)
    {
        $results = [];

        foreach ($this->items as $key => $value) {
            $results[$this->getGroupByKey($groupBy, $key, $value)][] = $value;
        }

        return new static($results);
    }

    /**
     * Ambil value 'key' dari method groupBy().
     *
     * @param callable|string $groupBy
     * @param string          $key
     * @param mixed           $value
     *
     * @return string
     */
    protected function getGroupByKey($groupBy, $key, $value)
    {
        if (!is_string($groupBy) && is_callable($groupBy)) {
            return $groupBy($value, $key);
        }

        return data_get($value, $groupBy);
    }

    /**
     * Beri key array asosiatif berdasarkan field.
     *
     * @param string $keyBy
     *
     * @return static
     */
    public function keyBy($keyBy)
    {
        $results = [];

        foreach ($this->items as $item) {
            $key = data_get($item, $keyBy);
            $results[$key] = $item;
        }

        return new static($results);
    }

    /**
     * Cek apakah item ada dalam collection berdasarkan key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Gabungkan value - value dari key yang diberikan menjadi string.
     *
     * @param string $value
     * @param string $glue
     *
     * @return string
     */
    public function implode($value, $glue = null)
    {
        return implode($glue, $this->lists($value));
    }

    /**
     * Intersect collection dengan item - item yang diberikan.
     *
     * @param \System\Support\Collection|array $items
     *
     * @return static
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Cek apakah collection kosong atau tidak.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Ambil semua key dari item - item collection.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }

    /**
     * Ambil item terakhir dari collection.
     *
     * @return mixed|null
     */
    public function last()
    {
        return count($this->items) > 0 ? end($this->items) : null;
    }

    /**
     * Ambil array dengan value - value dari key yang diberikan.
     *
     * @param string $value
     * @param string $key
     *
     * @return array
     */
    public function lists($value, $key = null)
    {
        return Arr::pluck($this->items, $value, $key);
    }

    /**
     * Jalankan callback ke masing - masing item collection.
     *
     * @param \Closure $callback
     *
     * @return static
     */
    public function map(Closure $callback)
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * Gabungkan collection dengan item - item yang diberikan.
     *
     * @param \System\Support\Collection|array $items
     *
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Ambil lalu hapus item terakhir dari collection.
     *
     * @return mixed|null
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Taruh item ke bagian awal collection.
     *
     * @param mixed $value
     */
    public function prepend($value)
    {
        array_unshift($this->items, $value);
    }

    /**
     * Taruh item ke bagian akhir collection.
     *
     * @param mixed $value
     */
    public function push($value)
    {
        $this->items[] = $value;
    }

    /**
     * Ambil sebuah value dari collection, dan hapus key-nya.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Taruh item ke collection berdasarkan key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function put($key, $value)
    {
        $this->items[$key] = $value;
    }

    /**
     * Ambil satu atau beberapa item secara acak dari collection.
     *
     * @param int $amount
     *
     * @return mixed
     */
    public function random($amount = 1)
    {
        if ($this->isEmpty()) {
            return;
        }

        $keys = array_rand($this->items, $amount);

        return is_array($keys)
            ? array_intersect_key($this->items, array_flip($keys))
            : $this->items[$keys];
    }

    /**
     * Reduce collection menjadi array tunggal.
     *
     * @param callable $callback
     * @param mixed    $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Buat collection dari semua elemen yang tidak lolos test kebenaran yang diberikan.
     *
     * @param \Closure|mixed $callback
     *
     * @return static
     */
    public function reject($callback)
    {
        if ($callback instanceof Closure) {
            return $this->filter(function ($item) use ($callback) {
                return !$callback($item);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Balik urutan item.
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * Cari value di collection dan return key-nya jika ketemu.
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        return array_search($value, $this->items, $strict);
    }

    /**
     * Ambil dan hapus item pertama dari collection.
     *
     * @return mixed|null
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Acak seluruh item di collection.
     *
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->items);

        return $this;
    }

    /**
     * Iris array collection.
     *
     * @param int  $offset
     * @param int  $length
     * @param bool $preserveKeys
     *
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * Potong array collection.
     *
     * @param int  $size
     * @param bool $preserveKeys
     *
     * @return static
     */
    public function chunk($size, $preserveKeys = false)
    {
        $chunks = new static();
        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $chunks->push(new static($chunk));
        }

        return $chunks;
    }

    /**
     * Urutkan tiap - tiap item menggunakan callback.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function sort(Closure $callback)
    {
        uasort($this->items, $callback);

        return $this;
    }

    /**
     * Urutkan collection menggunakan Closure.
     *
     * @param \Closure|string $callback
     * @param int             $options
     * @param bool            $descending
     *
     * @return $this
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        if (is_string($callback)) {
            $callback = $this->valueRetriever($callback);
        }

        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        $this->items = $results;

        return $this;
    }

    /**
     * Urutkan collection secara descending menggunakan Closure.
     *
     * @param \Closure|string $callback
     * @param int             $options
     *
     * @return $this
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sambung potongan array collection.
     *
     * @param int   $offset
     * @param int   $length
     * @param mixed $replacement
     *
     * @return static
     */
    public function splice($offset, $length = 0, $replacement = [])
    {
        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    /**
     * Ambil hasil jumlah dari value yang diberikan.
     *
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function sum($callback)
    {
        if (is_string($callback)) {
            $callback = $this->valueRetriever($callback);
        }

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result += $callback($item);
        }, 0);
    }

    /**
     * Ambil N item pertama atau terakhir.
     *
     * @param int $limit
     *
     * @return static
     */
    public function take($limit = null)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Ubah tiap - tiap item di collection menggunakan callback.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function transform(Closure $callback)
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * Return hanya item unik dari array collection.
     *
     * @return static
     */
    public function unique()
    {
        return new static(array_unique($this->items));
    }

    /**
     * Reset seluruh key pada array.
     *
     * @return static
     */
    public function values()
    {
        $this->items = array_values($this->items);

        return $this;
    }

    protected function valueRetriever($value)
    {
        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Ambil seluruh collection sebagai array biasa.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Menentukan data yang harus di-serialize menjadi json
     * method ini milik JSONSerializabe interface.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Ambil item - item collection sebagai json.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->items, $options);
    }

    /**
     * Ambil iterator untuk item - item collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Ambil instance CachingIterator.
     *
     * @param int $flags
     *
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Hitung jumlah item yang ada di collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Cek apakah item ada pada offset.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Ambil sebuah item berdasarkan offset yang diberikan.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set item ke offset yang diberikan.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset item pada offset yang diberikan.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Ubah collection menjadi string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Hasil array dari item - item collection.
     *
     * @param \System\Support\Collection|array $items
     *
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if ($items instanceof self) {
            $items = $items->all();
        }

        return $items;
    }
}
