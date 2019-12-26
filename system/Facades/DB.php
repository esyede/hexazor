<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

class DB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DB';
    }
}
