<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Config';
    }
}
