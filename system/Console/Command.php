<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

use Exception;

abstract class Command
{
    protected $console;

    protected $signature;

    protected $description;

    /**
     * Ambil signature command.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Ambil deskripsi command.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Definisikan objek kelas ini.
     */
    public function defineApp(Console $console)
    {
        if (!$this->console) {
            $this->console = $console;
        }
    }

    /**
     * Panggil method secara dimanis.
     *
     * @param string $method
     * @param mixed  $args
     *
     * @return bool
     */
    public function __call($method, $args)
    {
        if ($this->console && method_exists($this->console, $method)) {
            return call_user_func_array([$this->console, $method], $args);
        }

        $class = get_class($this);

        throw new Exception("Call to undefined method: {$class}::{$method}()");
    }
}
