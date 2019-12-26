<?php

namespace System\Libraries\Hash;

defined('DS') or exit('No direct script access allowed.');

use Exception;

class Hash
{
    /**
     * Buat hash password.
     *
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function make($value, array $options = [])
    {
        // skip deprecation warning di PHP 7.0.0+
        // ref: https://www.php.net/manual/en/function.password-hash.php
        unset($options['salt']);

        if (!isset($options['cost'])) {
            $options['cost'] = PASSWORD_BCRYPT_DEFAULT_COST;
        }

        $hashed = password_hash($value, PASSWORD_DEFAULT, $options);

        if (!is_string($hashed)) {
            throw new Exception('Malformatted password hash result.');
        }

        return $hashed;
    }

    /**
     * Cocokkan string password dengan hash-nya.
     *
     * @param string $value
     * @param string $hashed
     *
     * @return bool
     */
    public function check($value, $hashed)
    {
        return password_verify($value, $hashed);
    }

    public function needsRehash($hashed, array $options = [])
    {
        if (!isset($options['cost'])) {
            $options['cost'] = PASSWORD_BCRYPT_DEFAULT_COST;
        }

        return password_needs_rehash($hashed, PASSWORD_DEFAULT, $options);
    }
}
