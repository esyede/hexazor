<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

class Phone extends Module
{
    protected static $phoneNumber = null;

    public function phone($format = null)
    {
        if (null == $format) {
            $format = static::pickOne('phone_number');
        }

        return static::numberize($format);
    }

    public function number($format = null)
    {
        return $this->phone($format);
    }
}
