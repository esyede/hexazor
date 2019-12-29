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
        if (isset($this->container[$offset])) {
            return true;
        }

        $name = strtok($offset, '.');

        if (isset($this->container[$name])) {
            $p = $this->container[$name];
            while (false !== ($name = strtok('.'))) {
                if (!isset($p[$name])) {
                    return false;
                }
                $p = $p[$name];
            }

            $this->container[$offset] = $p;

            return true;
        }
        $name = str_replace(['\\', '/'], [DS, DS], $name);
        $path = $this->path.DS.$name.'.php';

        if (is_readable($path)) {
            $this->container[$name] = include $path;
            $file = $this->path.DS.$name.'.php';

            if (is_readable($file)) {
                $this->container[$name] = include $file;
            }

            return $this->offsetExists($offset);
        }

        return false;
    }

    /**
     * ArrayAccess.
     *
     * @param mixed $offset
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->container[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * ArrayAccess.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->container[$offset] = null;
        unset($this->container[$offset]);
    }
}
