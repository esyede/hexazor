<?php

namespace System\Support\Faker\Provider;

defined('DS') or exit('No direct script access allowed.');

class Company extends Base
{
    protected static $formats = [
        '{{lastName}} {{companySuffix}}',
    ];

    protected static $companySuffix = ['Ltd'];


    public function company()
    {
        $format = static::randomElement(static::$formats);
        return $this->generator->parse($format);
    }

    
    public static function companySuffix()
    {
        return static::randomElement(static::$companySuffix);
    }
}
