<?php

namespace System\Support\Faker\Provider\id;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Faker\Provider\Company as BaseCompany;

class Company extends BaseCompany
{
    protected static $formats = [
        '{{companyPrefix}} {{lastName}}',
        '{{companyPrefix}} {{lastName}} {{lastName}}',
        '{{companyPrefix}} {{lastName}} {{companySuffix}}',
        '{{companyPrefix}} {{lastName}} {{lastName}} {{companySuffix}}',
    ];

    protected static $companyPrefix = ['PT', 'CV', 'UD', 'PD', 'Perum'];
    protected static $companySuffix = ['(Persero) Tbk', 'Tbk'];


    public static function companyPrefix()
    {
        return static::randomElement(static::$companyPrefix);
    }

    
    public static function companySuffix()
    {
        return static::randomElement(static::$companySuffix);
    }
}
