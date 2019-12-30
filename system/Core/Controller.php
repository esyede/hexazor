<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use App\Http\Kernel;
use InvalidArgumentException;

class Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // code..
    }

    /**
     * Panggil local middleware.
     *
     * @param string|array $names
     * @param bool         $global
     */
    public function middleware($names)
    {
        $names = (array) $names;
        $locals = Kernel::$localMiddlewareGroups;

        foreach ($names as $name) {
            if (!isset($locals[$name])) {
                throw new InvalidArgumentException('No local middleware found with name: '.$name);
            }

            if (is_array($locals[$name])) {
                foreach ($locals[$name] as $class) {
                    if (!class_exists($class)) {
                        throw new InvalidArgumentException("Middleware class does not exists: {$class}");
                    }
                    
                    call_user_func_array([new $class(), 'handle'], []);
                }
            } else {
                if (!class_exists($locals[$name])) {
                    throw new InvalidArgumentException("Middleware class does not exists: {$locals[$name]}");
                }

                call_user_func_array([new $locals[$name](), 'handle'], []);
            }
        }
    }
}
