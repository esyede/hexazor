<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Faker;

class Address extends Module
{
    protected static $cityPrefix = null;
    protected static $citySuffix = null;
    protected static $streetSuffix = null;
    protected static $streetNumber = null;
    protected static $secondaryAddress = null;
    protected static $postalCode = null;
    protected static $stateName = null;
    protected static $stateAbbreviation = null;
    protected static $countryName = null;

    public function city()
    {
        $prefix = static::pickOne('city_prefix');
        $suffix = static::pickOne('city_suffix');

        switch (mt_rand(0, 4)) {
            default:
            
            case 0:
                return sprintf(
                    '%s %s%s',
                    static::pickOne('city_prefix'),
                    Faker::name()->first(),
                    static::pickOne('city_prefix')
                );
            
            case 1:
                return sprintf(
                    '%s %s',
                    static::pickOne('city_prefix'),
                    Faker::name()->first()
                );
            
            case 2:
                return sprintf(
                    '%s%s',
                    Faker::name()->first(),
                    static::pickOne('city_suffix')
                );
            
            case 3:
                return sprintf(
                    '%s%s',
                    Faker::name()->last(),
                    static::pickOne('city_suffix')
                );
        }
    }

    public function streetAddress($useSecondaryAddress = false)
    {
        $address = static::streetName().' '.static::numberize(static::pickOne('street_number'));

        if ($useSecondaryAddress) {
            $address .= ' '.static::secondaryAddress();
        }

        return $address;
    }

    public function streetName()
    {
        switch (mt_rand(0, 1)) {
            default:
            
            case 0:
                return sprintf(
                    '%s %s',
                    Faker::name()->last(),
                    static::pickOne('street_suffix')
                );

            case 1:
                return sprintf(
                    '%s %s',
                    Faker::name()->first(),
                    static::pickOne('street_suffix')
                );
        }
    }

    public function secondaryAddress()
    {
        return static::numberize(static::pickOne('secondary_address'));
    }

    public function postalCode()
    {
        return static::numberize(static::pickOne('postal_code'));
    }

    public function zipCode()
    {
        return static::postalCode();
    }

    public function zip()
    {
        return static::postalCode();
    }

    public function state()
    {
        return static::pickOne('state_name');
    }

    public function stateAbbreviation()
    {
        return static::pickOne('state_abbreviation');
    }

    public function country()
    {
        return static::pickOne('country_name');
    }
}
