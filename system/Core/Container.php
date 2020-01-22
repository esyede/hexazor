<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use ReflectionClass;

class Container
{
    protected static $registries = [];
    protected static $singletons = [];

    /**
     * Daftarkan object dan resolvernya.
     *
     * @param  string $name
     * @param  mixed  $resolver
     * @param  bool   $singleton
     *
     * @return void
     */
    public static function register($name, $resolver = null, $singleton = false)
    {
        if (is_null($resolver)) {
            $resolver = $name;
        }

        static::$registries[$name] = compact('resolver', 'singleton');
    }

    /**
     * Cek apakah object sudah didaftarkan ke container atau belum.
     *
     * @param  string $name
     *
     * @return bool
     */
    public static function registered($name)
    {
        return array_key_exists($name, static::$registries);
    }

    /**
     * Daftarkan object menggunakan singleton pattern.
     * Singleton pattern hanya akan meng-instance object sekali saja.
     *
     * @param  string $name
     * @param  mixed  $resolver
     *
     * @return void
     */
    public static function singleton($name, $resolver = null)
    {
        static::register($name, $resolver, true);
    }

    /**
     * Daftarkan ulang object yang sudah terdaftar menjadi singleton.
     *
     * @param  string $name
     * @param  mixed  $instance
     *
     * @return void
     */
    public static function instance($name, $instance)
    {
        static::$singletons[$name] = $instance;
    }

    /**
     * Resolve tipe instance yang diberikan.
     *
     * @param  string $type
     * @param  array  $parameters
     *
     * @return mixed
     */
    public static function resolve($type, $parameters = [])
    {
        $parameters = is_null($parameters) ? [] : $parameters;
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        if (isset(static::$singletons[$type])) {
            return static::$singletons[$type];
        }

        if (!isset(static::$registries[$type])) {
            $concrete = $type;
        } else {
            $concrete = array_get(static::$registries[$type], 'resolver', $type);
        }

        if ($concrete === $type || $concrete instanceof Closure) {
            $object = static::build($concrete, $parameters);
        } else {
            $object = static::resolve($concrete);
        }

        if (isset(static::$registries[$type]['singleton'])
        && static::$registries[$type]['singleton'] === true) {
            static::$singletons[$type] = $object;
        }

        return $object;
    }

    /**
     * Instansiasikan sebuah tipe instance yang diberikan.
     *
     * @param  string $type
     * @param  array  $parameters
     *
     * @return mixed
     */
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

    /**
     * Resolve semua dependensi dari \ReflectionParameter.
     *
     * @param  array $parameters
     * @param  array $arguments
     *
     * @return array
     */
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

    /**
     * Resolve parameter opsional untuk dependency injection.
     *
     * @param  \ReflectionParameter $parameter
     *
     * @return mixed
     */
    protected static function resolveNonClass($parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        } else {
            throw new Exception("Unresolvable dependency resolving [$parameter].");
        }
    }

    /**
     * Ambil seluruh data object di registry.
     *
     * @return array
     */
    public static function registries()
    {
        return static::$registries;
    }

    /**
     * Ambil seluruh data object singleton.
     *
     * @return array
     */
    public static function singletons()
    {
        return static::$singletons;
    }
}
