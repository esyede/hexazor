<?php

namespace System\Facades;

defined('DS') or exit('No direct script access allowed.');

class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Form';
    }
}
