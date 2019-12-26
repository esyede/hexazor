<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

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
    protected function middleware($names)
    {
        $names = (array) $names;
        $locals = Config::get('middlewares.locals', []);

        foreach ($names as $name) {
            if (!isset($locals[$name])) {
                throw new InvalidArgumentException('No local middleware found with name: '.$name);
            }

            if (!class_exists($locals[$name])) {
                throw new InvalidArgumentException("No local middleware class found with name: {$name}");
            }

            call_user_func_array([new $locals[$name](), 'handle'], []);
        }
    }
}
