<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Faker;

class Internet extends Module
{
    protected static $freeEmailDomain = null;
    protected static $domainSuffix = null;

    public function email($name = null)
    {
        return sprintf('%s@%s', $this->userName($name), $this->domainName());
    }

    public function freeEmail($name = null)
    {
        return sprintf('%s@%s', $this->userName($name), static::pickOne('free_email_domain'));
    }

    public function userName($name = null)
    {
        if (null == $name) {
            if (0 == mt_rand(0, 1)) {
                $name = Faker::name()->first();
            } else {
                $name = sprintf('%s_%s', Faker::name()->first(), Faker::name()->last());
            }
        }

        return preg_replace('/[\W]/', '', str_replace(' ', '.', strtolower($name)));
    }

    public function domainName()
    {
        return sprintf('%s.%s', $this->domainWord(), static::pickOne('domain_suffix'));
    }

    public function domainWord()
    {
        return strtolower(preg_replace('/[\W]/', '', explode(' ', Faker::name()->last())[0]));
    }

    public function ipv4()
    {
        return sprintf(
            '%d.%d.%d.%d',
            mt_rand(2, 255),
            mt_rand(2, 255),
            mt_rand(2, 255),
            mt_rand(2, 255)
        );
    }

    public function ipv6()
    {
        return sprintf(
            '%x:%x:%x:%x:%x:%x:%x:%x',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}
