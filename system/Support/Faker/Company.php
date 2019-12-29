<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Faker;

class Company extends Module
{
    protected static $companySuffix = null;
    protected static $catchPhrase = null;

    public function name()
    {
        switch (mt_rand(0, 2)) {
            default:

            case 0:
                return sprintf(
                    '%s %s',
                    Faker::name()->last(),
                    static::pickOne('company_suffix')
                );

            case 1:
                return sprintf(
                    '%s %s %s',
                    Faker::name()->last(),
                    Faker::name()->last(),
                    static::pickOne('company_suffix')
                );

            case 2:
                return sprintf(
                    '%s-%s %s',
                    Faker::name()->last(),
                    Faker::name()->last(),
                    static::pickOne('company_suffix')
                );
        }
    }

    public function catchPhrase()
    {
        static::loadData('catch_phrase');

        return implode(
            ' ',
            [
                static::$catchPhrase[0][array_rand(static::$catchPhrase[0])],
                static::$catchPhrase[1][array_rand(static::$catchPhrase[1])],
                static::$catchPhrase[2][array_rand(static::$catchPhrase[2])],
            ]
        );
    }
}
