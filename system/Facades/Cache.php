<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Cache';
    }
}
