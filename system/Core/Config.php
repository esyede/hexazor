<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use ArrayAccess;

class Config implements ArrayAccess
{
    protected $container = [];
    protected $path;

    protected static $instance;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->path = base_path('config/');

        if (!static::$instance) {
            static::$instance = $this;
        }
    }

    /**
     * Inisialiasasi.
     *
     * @return static
     */
    public static function init()
    {
        if (!static::$instance) {
            static::$instance = new self();
        } else {
            static::$instance->path = base_path('config/');
            $files = glob(static::$instance->path.'*.php');

            foreach ($files as $file) {
                $name = basename($file, '.php');
                static::$instance->container[$name] = require_once $file;
            }
        }

        return static::$instance;
    }

    /**
     * Ambil data config.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (!static::$instance) {
            static::init();
        }

        return static::$instance->offsetGet($name) ?: $default;
    }

    /**
     * Set data config.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function set($key, $value)
    {
        if (!static::$instance) {
            static::init();
        }

        if (!array_key_exists($key, static::$instance->container)) {
            return false;
        }

        static::$instance->offsetGet($key);
        static::$instance->container[$key] = $value;

        return true;
    }

    /**
     * Cek apakah offset config ada.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function has($key)
    {
        if (!static::$instance) {
            static::init();
        }

        return static::$instance->offsetExists($key);
    }

    public static function all()
    {
        if (!static::$instance) {
            static::init();
        }

        return static::$instance->container;
    }

    /**
     * ArrayAccess.
     *
     * @param mixed $offset
     */
    public function offsetExists($offset)
    {
        if (isset(static::$instance->container[$offset])) {
            return true;
        }

        $name = strtok($offset, '.');

        if (isset(static::$instance->container[$name])) {
            $p = static::$instance->container[$name];
            while (false !== ($name = strtok('.'))) {
                if (!isset($p[$name])) {
                    return false;
                }
                $p = $p[$name];
            }

            static::$instance->container[$offset] = $p;

            return true;
        }
        $name = str_replace(['\\', '/'], [DS, DS], $name);
        $path = static::$instance->path.DS.$name.'.php';

        if (is_readable($path)) {
            static::$instance->container[$name] = include $path;
            $file = static::$instance->path.DS.$name.'.php';

            if (is_readable($file)) {
                static::$instance->container[$name] = include $file;
            }

            return static::$instance->offsetExists($offset);
        }

        return false;
    }

    /**
     * ArrayAccess offsetGet.
     *
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return static::$instance->offsetExists($offset)
            ? static::$instance->container[$offset]
            : null;
    }

    /**
     * ArrayAccess offsetSet.
     *
     * @param string $offset
     * @param mixed $value
     *
     * @return bool
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            static::$instance->container[] = $value;
        } else {
            static::$instance->container[$offset] = $value;
        }

        return static::$instance->container[$offset] === $value;
    }

    /**
     * ArrayAccess.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetUnset($offset)
    {
        static::$instance->container[$offset] = null;
        unset(static::$instance->container[$offset]);

        return false === isset(static::$instance->container[$offset]);
    }
}
