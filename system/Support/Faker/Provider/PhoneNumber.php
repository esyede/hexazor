<?php

namespace System\Support\Faker\Provider;

defined('DS') or exit('No direct script access allowed.');

class PhoneNumber extends Base
{
    protected static $formats = ['###-###-###'];


    public static function phoneNumber()
    {
        return static::numerify(static::randomElement(static::$formats));
    }
}
