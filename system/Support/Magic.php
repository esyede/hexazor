<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

class Magic
{
    public $attributes = [];

    /**
     * Buat instance kelas baru.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes = (array) $attributes;

        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Ambil atribut dari container.
     *
     * @param string $attribute
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($attribute, $default = null)
    {
        return array_get($this->attributes, $attribute, $default);
    }

    /**
     * Tangani pemanggilan method dinamis saat set atribut.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $parameters = (array) $parameters;
        $this->$method = (count($parameters) > 0) ? $parameters[0] : true;

        return $this;
    }

    /**
     * Ambil value atribut secara dinamis.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Set value atribut secara dinamis.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Cek apakah value atribut sudah di set atau belum secara dinamis.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset atribut secara dinamis.
     *
     * @param string $key
     *
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}
