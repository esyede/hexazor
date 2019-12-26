<?php

namespace System\Libraries\Crypt;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Config;

class Crypt
{
    /**
     * Enkripsi string.
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        $key = Config::get('app.application_key');
        $iv = random_bytes(16);

        $cipher = openssl_encrypt(
            $data,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );

        if (false === $cipher) {
            throw new Exception('Could not encrypt the data.');
        }

        $hmac = hash_hmac(
            'sha256',
            $iv.$cipher,
            mb_substr($key, 32, null, '8bit'),
            true
        );

        $encrypted = base64_encode($hmac.$iv.$cipher);

        return $encrypted;
    }

    /**
     * Dekripsi string.
     *
     * @param string $data
     *
     * @return string
     */
    public function decrypt($data)
    {
        $data = base64_decode($data);
        $key = Config::get('app.application_key');
        $hmac = mb_substr($data, 0, 32, '8bit');
        $iv = mb_substr($data, 32, 16, '8bit');
        $cipher = mb_substr($data, 48, null, '8bit');

        $hmac2 = hash_hmac(
            'sha256',
            $iv.$cipher,
            mb_substr($key, 32, null, '8bit'),
            true
        );

        if (!hash_equals($hmac, $hmac2)) {
            throw new Exception('Hash verification failed.');
        }

        $decrypted = openssl_decrypt(
            $cipher,
            'AES-256-CBC',
            mb_substr($key, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $iv
        );

        if (false === $decrypted) {
            throw new Exception('Could not decrypt the data.');
        }

        return $decrypted;
    }
}
