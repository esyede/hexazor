<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;
use System\Core\Config;

class Factory
{
    protected static $defaultProviders = [
        'Address', 'Barcode', 'Biased', 'Color',
        'Company', 'DateTime', 'File', 'Image',
        'Internet', 'Lorem', 'Miscellaneous',
        'Payment', 'Person', 'PhoneNumber', 'Text',
        'UserAgent', 'Uuid'
    ];


    public static function create($locale = null)
    {
        $locale = is_null($locale) ? Config::get('app.default_language', 'en') : $locale;
        
        $availableLocales = glob(system_path('Support/Faker/Provider/*'), GLOB_ONLYDIR);
        $availableLocales = array_map(function ($item) {
            $item = explode(DS, $item);
            return end($item);
        }, $availableLocales);

        if (!in_array($locale, $availableLocales)) {
            $locale = system_path('Support/Faker/Provider/'.$locale);
            throw new InvalidArgumentException('Faker locale folder not found: '.$locale);
        }
        
        $generator = new Generator();

        foreach (static::$defaultProviders as $provider) {
            $providerClassName = self::getProviderClassname($provider, $locale);
            $generator->addProvider(new $providerClassName($generator));
        }

        return $generator;
    }


    protected static function getProviderClassname($provider, $locale = '')
    {
        if ($providerClass = self::findProviderClassname($provider, $locale)) {
            return $providerClass;
        }

        $defaultLocale = Config::get('app.default_language', 'en');
        if ($providerClass = self::findProviderClassname($provider, $defaultLocale)) {
            return $providerClass;
        }

        if ($providerClass = self::findProviderClassname($provider)) {
            return $providerClass;
        }

        throw new InvalidArgumentException("Unable to find provider [$provider] with locale [$locale]");
    }


    protected static function findProviderClassname($provider, $locale = '')
    {
        $providerClass = '\\'.__NAMESPACE__.'\\Provider\\'.(filled($locale) ? $locale.'\\' : '').$provider;
        
        if (class_exists($providerClass, true)) {
            return $providerClass;
        }
    }
}
