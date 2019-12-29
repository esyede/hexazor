<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class Faker
{
    protected static $instances = [];

    public static function __callStatic($name, $parameters)
    {
        $name = Str::studly($name);

        if (!isset(static::$instances[$name])) {
            $class = __NAMESPACE__.'\\Faker\\'.$name;

            if (!class_exists($class)) {
                throw new InvalidArgumentException('No such faker data class: '.$name);
            }

            static::$instances[$name] = new $class();
        }

        return static::$instances[$name];
    }

    public static function make($name)
    {
        return static::__callStatic($name, null);
    }
}
