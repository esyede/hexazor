<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

use RuntimeException;
use InvalidArgumentException;

abstract class Facade
{
    protected static $applications;
    protected static $resolved = [];
    protected static $created = [];

    protected static function resolveInstance($facadeName)
    {
        if (is_object($facadeName)) {
            return $facadeName;
        }

        if (isset(static::$resolved[$facadeName])) {
            return static::$resolved[$facadeName];
        }

        return static::$resolved[$facadeName] = static::$applications['providers'][$facadeName];
    }

    public static function setFacadeApplication($app)
    {
        static::$applications = $app;
    }

    public static function getFacadeApplication()
    {
        return static::$applications;
    }

    public static function clearResolvedInstance($facadeName)
    {
        unset(static::$resolved[$facadeName]);
    }

    public static function __callStatic($method, $params)
    {
        $accessor = static::getFacadeAccessor();
        $provider = static::resolveInstance($accessor);

        if (!isset(static::$created[$accessor])) {
            if (!class_exists($provider)) {
                throw new RuntimeException('Unable to resolve facade class: '.$accessor);
                exit();
            }

            static::$created[$accessor] = new $provider();
        }

        return call_user_func_array([static::$created[$accessor], $method], $params);
    }
}
