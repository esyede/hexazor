<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use ReflectionClass;

class Container
{
    public static $registry = [];
    public static $singletons = [];

    
    public static function register($name, $resolver = null, $singleton = false)
    {
        if (is_null($resolver)) {
            $resolver = $name;
        }

        static::$registry[$name] = compact('resolver', 'singleton');
    }

    
    public static function registered($name)
    {
        return array_key_exists($name, static::$registry);
    }

    
    public static function singleton($name, $resolver = null)
    {
        static::register($name, $resolver, true);
    }

    
    public static function instance($name, $instance)
    {
        static::$singletons[$name] = $instance;
    }

    
    public static function resolve($type, $parameters = [])
    {
        $parameters = is_null($parameters) ? [] : $parameters;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if (isset(static::$singletons[$type])) {
            return static::$singletons[$type];
        }

        if (!isset(static::$registry[$type])) {
            $concrete = $type;
        } else {
            $concrete = array_get(static::$registry[$type], 'resolver', $type);
        }

        if ($concrete === $type || $concrete instanceof Closure) {
            $object = static::build($concrete, $parameters);
        } else {
            $object = static::resolve($concrete);
        }

        if (isset(static::$registry[$type]['singleton'])
        && static::$registry[$type]['singleton'] === true) {
            static::$singletons[$type] = $object;
        }

        return $object;
    }

    
    protected static function build($type, $parameters = [])
    {
        $parameters = is_null($parameters) ? [] : $parameters;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if ($type instanceof Closure) {
            return call_user_func_array($type, $parameters);
        }

        $reflector = new ReflectionClass($type);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Resolution target [$type] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $type;
        }

        $dependencies = static::dependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    
    protected static function dependencies($parameters, $arguments)
    {
        $parameters = is_null($parameters) ? [] : $parameters;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        $arguments = is_null($arguments) ? [] : $arguments;
        $arguments = is_array($arguments) ? $arguments : [$arguments];

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (count($arguments) > 0) {
                $dependencies[] = array_shift($arguments);
            } elseif (is_null($dependency)) {
                $dependency[] = static::resolveNonClass($parameter);
            } else {
                $dependencies[] = static::resolve($dependency->name);
            }
        }

        return (array) $dependencies;
    }

    
    protected static function resolveNonClass($parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        } else {
            throw new Exception("Unresolvable dependency resolving [$parameter].");
        }
    }
}
