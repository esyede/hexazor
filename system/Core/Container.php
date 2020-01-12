<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use ArrayAccess;
use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionParameter;

class Container implements ArrayAccess
{
    protected $resolved = [];
    protected $bindings = [];
    protected $instances = [];
    protected $aliases = [];
    protected $reboundCallbacks = [];
    protected $resolvingCallbacks = [];
    protected $globalResolvingCallbacks = [];


    protected function resolvable($abstract)
    {
        return $this->bound($abstract) || $this->isAlias($abstract);
    }


    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }


    public function resolved($abstract)
    {
        return isset($this->resolved[$abstract]) || isset($this->instances[$abstract]);
    }


    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }


    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);
            $this->alias($abstract, $alias);
        }

        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (! $concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');

        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }


    protected function getClosure($abstract, $concrete)
    {
        return function ($c, $parameters = []) use ($abstract, $concrete) {
            $method = ($abstract == $concrete) ? 'build' : 'make';

            return $c->$method($concrete, $parameters);
        };
    }


    public function bindIf($abstract, $concrete = null, $shared = false)
    {
        if (! $this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }


    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }


    public function share(Closure $closure)
    {
        return function ($container) use ($closure) {
            static $object;

            if (is_null($object)) {
                $object = $closure($container);
            }

            return $object;
        };
    }


    public function bindShared($abstract, Closure $closure)
    {
        $this->bind($abstract, $this->share($closure), true);
    }


    public function extend($abstract, Closure $closure)
    {
        if (! isset($this->bindings[$abstract])) {
            throw new InvalidArgumentException("Type {$abstract} is not bound.");
        }

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);
            $this->rebound($abstract);
        } else {
            $extender = $this->getExtender($abstract, $closure);
            $this->bind($abstract, $extender, $this->isShared($abstract));
        }
    }


    protected function getExtender($abstract, Closure $closure)
    {
        $resolver = $this->bindings[$abstract]['concrete'];

        return function ($container) use ($resolver, $closure) {
            return $closure($resolver($container), $container);
        };
    }


    public function instance($abstract, $instance)
    {
        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);
            $this->alias($abstract, $alias);
        }

        unset($this->aliases[$abstract]);
        $bound = $this->bound($abstract);
        $this->instances[$abstract] = $instance;

        if ($bound) {
            $this->rebound($abstract);
        }
    }


    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
    }


    protected function extractAlias(array $definition)
    {
        return [key($definition), current($definition)];
    }


    public function rebinding($abstract, Closure $callback)
    {
        $this->reboundCallbacks[$abstract][] = $callback;

        if ($this->bound($abstract)) {
            return $this->make($abstract);
        }
    }


    public function refresh($abstract, $target, $method)
    {
        return $this->rebinding($abstract, function ($app, $instance) use ($target, $method) {
            $target->{$method}($instance);
        });
    }


    protected function rebound($abstract)
    {
        $instance = $this->make($abstract);

        foreach ($this->getReboundCallbacks($abstract) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }


    protected function getReboundCallbacks($abstract)
    {
        if (isset($this->reboundCallbacks[$abstract])) {
            return $this->reboundCallbacks[$abstract];
        }

        return [];
    }


    public function make($abstract, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        $this->fireResolvingCallbacks($abstract, $object);
        $this->resolved[$abstract] = true;

        return $object;
    }


    protected function getConcrete($abstract)
    {
        if (! isset($this->bindings[$abstract])) {
            if ($this->missingLeadingSlash($abstract) && isset($this->bindings['\\'.$abstract])) {
                $abstract = '\\'.$abstract;
            }

            return $abstract;
        }

        return $this->bindings[$abstract]['concrete'];
    }


    protected function missingLeadingSlash($abstract)
    {
        return is_string($abstract) && strpos($abstract, '\\') !== 0;
    }


    public function build($concrete, $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            $message = "Target [$concrete] is not instantiable.";

            throw new Exception($message);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $parameters = $this->keyParametersByArgument($dependencies, $parameters);
        $instances = $this->getDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }


    protected function getDependencies($parameters, array $primitives = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return (array) $dependencies;
    }


    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $class = $parameter->getDeclaringClass()->getName();
        $message = "Unresolvable dependency resolving [$parameter] in class {$class}";

        throw new Exception($message);
    }


    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        } catch (Exception $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }

    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);
                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }


    public function resolving($abstract, Closure $callback)
    {
        $this->resolvingCallbacks[$abstract][] = $callback;
    }


    public function resolvingAny(Closure $callback)
    {
        $this->globalResolvingCallbacks[] = $callback;
    }


    protected function fireResolvingCallbacks($abstract, $object)
    {
        if (isset($this->resolvingCallbacks[$abstract])) {
            $this->fireCallbackArray($object, $this->resolvingCallbacks[$abstract]);
        }

        $this->fireCallbackArray($object, $this->globalResolvingCallbacks);
    }


    protected function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $object, $this);
        }
    }


    public function isShared($abstract)
    {
        if (isset($this->bindings[$abstract]['shared'])) {
            $shared = $this->bindings[$abstract]['shared'];
        } else {
            $shared = false;
        }

        return isset($this->instances[$abstract]) || $shared === true;
    }


    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }


    protected function getAlias($abstract)
    {
        return isset($this->aliases[$abstract]) ? $this->aliases[$abstract] : $abstract;
    }


    public function getBindings()
    {
        return $this->bindings;
    }


    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }


    public function forgetInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }


    public function forgetInstances()
    {
        $this->instances = [];
    }


    public function offsetExists($key)
    {
        return isset($this->bindings[$key]);
    }


    public function offsetGet($key)
    {
        return $this->make($key);
    }


    public function offsetSet($key, $value)
    {
        if (! $value instanceof Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->bind($key, $value);
    }


    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }


    public function __get($key)
    {
        return $this[$key];
    }


    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}
