<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

class Name extends Module
{
    protected static $firstName = null;
    protected static $lastName = null;

    public function name()
    {
        return static::firstName().' '.static::lastName();
    }

    public function firstName()
    {
        return static::pickOne('first_name');
    }

    public function first()
    {
        return $this->firstName();
    }

    public function lastName()
    {
        return static::pickOne('last_name');
    }

    public function last()
    {
        return $this->lastName();
    }
}
