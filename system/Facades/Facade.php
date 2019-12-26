<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

use RuntimeException;

abstract class Facade
{
    protected static $applications;
    protected static $reselovedInstance = [];
    protected static $createdInstances = [];

    protected static function resolveInstance($facadeName)
    {
        if (is_object($facadeName)) {
            return $facadeName;
        }

        if (isset(static::$reselovedInstance[$facadeName])) {
            return static::$reselovedInstance[$facadeName];
        }

        return static::$reselovedInstance[$facadeName] = static::$applications['providers'][$facadeName];
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
        unset(static::$reselovedInstance[$facadeName]);
    }

    public static function __callStatic($method, $params)
    {
        $accessor = static::getFacadeAccessor();
        $provider = static::resolveInstance($accessor);

        if (!isset(static::$createdInstances[$accessor])) {
            if (!class_exists($provider)) {
                throw new RuntimeException('Unable to resolve facade class: '.$accessor);
            }

            static::$createdInstances[$accessor] = new $provider();
        }

        return call_user_func_array([static::$createdInstances[$accessor], $method], $params);
    }
}
