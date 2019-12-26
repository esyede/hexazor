<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Request';
    }
}
