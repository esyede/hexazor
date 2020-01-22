<?php

namespace System\Console\Traits;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;
use System\Console\Console;

trait Writer
{
    public function write($message, $indent = 0)
    {
        if (!Console::isSilentMode()) {
            $message = is_cli() ? $message : '<pre>'.$message.'</pre>';
            print str_repeat(' ', (int) $indent).$message;
        }
    }


    public function writeline($message, $indent = 0)
    {
        $this->write($message, $indent);
        $this->newline();
    }


    public function newline($amount = 1)
    {
        if (!Console::isSilentMode()) {
            print str_repeat(PHP_EOL, $amount);
        }
    }


    public static function __callStatic($method, $parameters)
    {
        if (!method_exists($this, $method)) {
            $class = get_class($this);
            throw new InvalidArgumentException("Method [$method] does not exists in $class trait");
        }

        call_user_func_array([$this, $method], (array) $parameters);
    }


    public function __call($method, $parameters)
    {
        if (!method_exists($this, $method)) {
            $class = get_class($this);
            throw new InvalidArgumentException("Method [$method] does not exists in $class trait");
        }

        call_user_func_array([$this, $method], (array) $parameters);
    }
}
