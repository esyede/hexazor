<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class Config
{
    protected static $files = [];
    protected static $data = [];

    /**
     * Inisialisasi.
     *
     * @return void
     */
    public static function initialize()
    {
        if (empty(static::$files)) {
            static::$files = glob(base_path('config/*.php'));
        }

        if (empty(static::$data)) {
            static::load(static::$files);
        }
    }

    /**
     * Ambil data config.
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $data = &static::$data;
        $keys = explode('.', $key);

        foreach ($keys as $test) {
            $direct = implode('.', $keys);
            if (is_array($data) && array_key_exists($direct, $data)) {
                return $data[$direct];
            }

            if (!is_array($data) || !array_key_exists($test, $data)) {
                return $default;
            }

            $data = &$data[$test];
            array_shift($keys);
        }

        return $data;
    }

    /**
     * Set data config.
     *
     * @param string|array $key
     * @param mixed        $value
     */
    public static function set($key, $value = null)
    {
        if (is_array($key)) {
            return static::merge($key);
        }

        if (func_num_args() < 2) {
            throw new InvalidArgumentException(
                'Too few arguments passed to Config::set(). Expect 2, got 1'
            );
        }

        $data = &static::$data;
        $segments = explode('.', $key);

        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            
            $data = &$data[$segment];
        }

        return $data[array_shift($segments)] = $value;
    }

    /**
     * Hapus satu atau beberapa item konfigurasi.
     *
     * @param  string|array $keys
     *
     * @return void
     */
    public static function forget($keys)
    {
        $original = &static::$data;
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset(static::$data[$part]) && is_array(static::$data[$part])) {
                    static::$data = &static::$data[$part];
                }
            }

            unset(static::$data[array_shift($parts)]);
            static::$data = &$original;
        }
    }

    /**
     * Push item ke bagian akhir array konfigurasi.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public static function push($key, $value)
    {
        $items = static::get($key);
        
        if (!is_array($items)) {
            throw new InvalidArgumentException(
                'Expected the target to be array, got '.gettype($items)
            );
        }

        $items[] = $value;
        static::set($key, $items);
    }

    /**
     * Merge beberapa item ke array konfigurasi.
     * Alias untuk set() dengan hanya satu parammeter.
     *
     * @param  array  $data
     *
     * @return void
     */
    public static function override(array $data)
    {
        static::merge($data);
    }

    /**
     * Cek apakah key ada dalam array konfigurasi
     *
     * @param  string $key
     *
     * @return bool
     */
    public static function has($key)
    {
        $data = &static::$data;
        $keys = explode('.', $key);

        foreach ($keys as $test) {
            $direct = implode('.', $keys);

            if (is_array($data) && array_key_exists($direct, $data)) {
                return true;
            }

            if (!is_array($data) || !array_key_exists($test, $data)) {
                return false;
            }

            $data = &$data[$test];
            array_shift($keys);
        }

        return true;
    }

    /**
     * Alias untuk method has().
     *
     * @param  string $key
     *
     * @return bool
     */
    public static function exists($key)
    {
        return static::has($key);
    }

    /**
     * Alias untuk method data().
     *
     * @return array
     */
    public static function all()
    {
        return static::data();
    }

    /**
     * Ambil seluruh array konfigurasi.
     *
     * @return array
     */
    public static function data()
    {
        return static::$data;
    }

    /**
     * List semua file konfigurasi.
     *
     * @return array
     */
    public static function files()
    {
        return static::$files;
    }

    /**
     * Muat satu atau beberapa file konfigurasi.
     *
     * @param  string|array $files
     * @param  bool         $forceReload
     *
     * @return void
     */
    public static function load($files, $forceReload = false)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ((array_key_exists($file, static::$files) && !$forceReload) || !is_file($file)) {
                unset(static::$files[$file]);
                continue;
            }

            $data[basename($file, '.php')] = static::requiring($file);

            if ($data && is_array($data)) {
                static::override($data);
                static::$files[$file] = true;
            }

            unset($data);
        }
    }

    /**
     * Cek apakah file konfigurasi sudah di-load atau belum.
     *
     * @param  string $file
     *
     * @return bool
     */
    public static function loaded($file)
    {
        return array_key_exists($file, static::$files);
    }

    /**
     * Merge array ke konfigurasi secara rekursif.
     *
     * @param  array  $data
     *
     * @return void
     */
    protected static function merge(array $data)
    {
        static::$data = array_replace_recursive(static::$data, $data);
    }

    /**
     * Include file.
     *
     * @param  string $path
     *
     * @return mixed
     */
    private static function requiring($path)
    {
        return require_once $path;
    }
}
