<?php

namespace System\Support\Faker\Calculator;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class TCNo
{
    public static function checksum($identityPrefix)
    {
        if (strlen((string) $identityPrefix) !== 9) {
            throw new InvalidArgumentException('Argument should be an integer and should be 9 digits.');
        }

        $oddSum = 0;
        $evenSum = 0;
        $identityArray = array_map('intval', str_split($identityPrefix));

        foreach ($identityArray as $index => $digit) {
            if ($index % 2 === 0) {
                $evenSum += $digit;
            } else {
                $oddSum += $digit;
            }
        }

        $tenthDigit = (7 * $evenSum - $oddSum) % 10;
        $eleventhDigit = ($evenSum + $oddSum + $tenthDigit) % 10;

        return $tenthDigit.$eleventhDigit;
    }


    public static function isValid($tcNo)
    {
        return self::checksum(substr($tcNo, 0, -2)) === substr($tcNo, -2, 2);
    }
}
