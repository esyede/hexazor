<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

use ArrayAccess;
use InvalidArgumentException;

class Arr
{
    /**
     * Periksa apakah value yang diberikan merupakan array dan dapat diakses.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Tambahkan sebuah elemen ke array menggunakan dot-notation (jika belum ada).
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Collapse sebuah array bersarang menjadi sebuah array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        $results = call_user_func_array('array_merge', $results);

        return $results;
    }

    /**
     * Gabungkan silang array - array yang diberikan, lalu return semua permutasi yang mungkin.
     *
     * @param array ...$arrays
     *
     * @return array
     */
    public static function crossJoin(/* ...arrays */)
    {
        $arrays = func_get_args();

        if (blank($arrays)) {
            return [];
        }

        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];
            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;
                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }

    /**
     * Membagi array menjadi dua array. Satu berdasarkan key dan satu lagi berdasarkan value.
     *
     * @param array $array
     *
     * @return array
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Ratakan array asosiatif multi-dimensi dengan dot.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public static function dot(array $array, $prepend = '')
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Ambil semua array kecuali key yang ditentukan.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Ambil array yang di-flatten() dari elemen array bersarang.
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    public static function fetch($array, $key)
    {
        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            $results = [];

            foreach ($array as $value) {
                if (array_key_exists($segment, $value = (array) $value)) {
                    $results[] = $value[$segment];
                }
            }

            $array = array_values($results);
        }

        return array_values($results);
    }

    /**
     * Cek apakah key yang diberikan ada di array.
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Me-return elemen pertama dalam array yang melewati tes kebenaran yang diberikan.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * Me-return elemen terakhir dalam array yang melewati tes kebenaran yang diberikan.
     *
     * @param array         $array
     * @param callable|null $callback
     * @param mixed         $default
     *
     * @return mixed
     */
    public static function last($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Ratakan array multi-dimensi menjadi satu level.
     *
     * @param array $array
     * @param int   $depth
     *
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        foreach ($array as $item) {
            $item = ($item instanceof Collection) ? $item->all() : $item;

            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = (1 === $depth)
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Hapus satu atau beberapa item array menggunakan notasi "dot".
     *
     * @param array        $array
     * @param array|string $keys
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }

            unset($array[array_shift($parts)]);
            $array = &$original;
        }
    }

    /**
     * Ambil item dari array menggunakan notasi "dot".
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $key
     * @param mixed              $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (false === strpos($key, '.')) {
            return isset($array[$key]) ? $array[$key] : value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * Cek apakah ada satu atau beberapa item dalam array menggunakan notasi "dot".
     *
     * @param \ArrayAccess|array $array
     * @param string|array       $keys
     *
     * @return bool
     */
    public static function has($array, $keys)
    {
        $keys = (array) $keys;
        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;
            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray)
                && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Cek apakah sebuah array merupakan array asosiatif atau bukan.
     * Sebuah array dikatakan asosiatif jika tidak mengandung key numerik urut yang dimulai dari nol.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Ambil subset item dari array yang diberikan.
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        $keys = (array) $keys;

        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Ambil array nilai dari array.
     *
     * @param array             $array
     * @param string|array      $value
     * @param string|array|null $key
     *
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        list($value, $key) = static::explodePluckParameters($value, $key);

        $results = [];
        foreach ($array as $item) {
            $itemValue = data_get($item, $value);
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Explode value dan key dari argumen yang dioper ke method pluck().
     *
     * @param string|array      $value
     * @param string|array|null $key
     *
     * @return array
     */
    protected static function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;
        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Taruh item ke awal array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     *
     * @return array
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Ambil sebuah value dari array, dan hapus key-nya.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function pull(array &$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);

        return $value;
    }

    /**
     * Ambil satu atau beberapa nilai acak dari array.
     *
     * @param array    $array
     * @param int|null $number
     *
     * @return mixed
     */
    public static function random($array, $number = null)
    {
        $requested = is_null($number) ? 1 : $number;
        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException("You requested {$requested} items, but there are only {$count} items available.");
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if (0 === (int) $number) {
            return [];
        }

        $keys = array_rand($array, $number);
        $keys = (array) $keys;

        $results = [];
        foreach ($keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Set item array ke value yang diberikan menggunakan notasi "dot"
     * Jika tidak ada key yang diberikan untuk method ini, seluruh array akan di-replace.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Acak array yang diberikan dan kembalikan hasilnya.
     *
     * @param array    $array
     * @param int|null $seed
     *
     * @return array
     */
    public static function shuffle($array, $seed = null)
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }

        return $array;
    }

    /**
     * Urutkan array menggunakan callback atau menggunakan notasi "dot".
     *
     * @param array                $array
     * @param callable|string|null $callback
     *
     * @return array
     */
    public static function sort($array, $callback = null)
    {
        return Collection::make($array)->sortBy($callback)->all();
    }

    /**
     * Urutkan array berdasarkan key dan value secara rekursif.
     *
     * @param array $array
     *
     * @return array
     */
    public static function sortRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * Ubah array menjadi query string.
     *
     * @param array $array
     *
     * @return string
     */
    public static function query($array)
    {
        return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * Filter array menggunakan callback.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Jika value yang diberikan bukan array dan bukan null, bungkus kedalam array.
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}
