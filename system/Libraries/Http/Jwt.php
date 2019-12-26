<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

use Exception;

class Jwt
{
    private $exp = 60;

    private $leeway = 0;

    private $algorithms = [
        'HS256' => 'SHA256',
        'HS512' => 'SHA512',
        'HS384' => 'SHA384',
    ];

    /**
     * Buat JWT.
     *
     * @param array  $payload
     * @param string $secret
     * @param string $algorithm
     * @param array  $headers
     *
     * @return string
     */
    public function encode($payload, $secret, $algorithm = 'HS256', $headers = null)
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm,
        ];

        if (null !== $headers && is_array($headers)) {
            array_merge($headers, $header);
        }

        $payload['exp'] = time() + $this->exp;
        $payload['jti'] = uniqid(time());
        $payload['iat'] = time();

        $header = $this->urlSafeBase64Encode($this->jsonEncode($header));
        $payload = $this->urlSafeBase64Encode($this->jsonEncode($payload));
        $message = $header.'.'.$payload;

        $signature = $this->urlSafeBase64Encode($this->signature($message, $secret, $alg));

        return $header.'.'.$payload.'.'.$signature;
    }

    /**
     * Decode JWT.
     *
     * @param string $token
     * @param string $secret
     *
     * @return object
     */
    public function decode($token, $secret)
    {
        if (empty($secret)) {
            throw new Exception('Secret key cannot be empty');
        }

        $jwt = explode('.', $token);
        if (3 != count($jwt)) {
            throw new Exception('Wrong number of segments: '.count($jwt));
        }

        list($head64, $payload64, $sign64) = $jwt;
        if (null === ($header = $this->jsonDecode($this->urlSafeBase64Decode($head64)))) {
            throw new Exception('Invalid header encoding');
        }

        if (null === ($payload = $this->jsonDecode($this->urlSafeBase64Decode($payload64)))) {
            throw new Exception('Invalid claims encoding');
        }

        if (false === ($signature = $this->urlSafeBase64Decode($sign64))) {
            throw new Exception('Invalid signature encoding');
        }

        if (empty($header->alg)) {
            throw new Exception('Empty algorithm');
        }

        if (empty($this->algorithms[$header->alg])) {
            throw new Exception('Algorithm not supported: '.$header->alg);
        }

        if (!$this->verify("$head64.$payload64", $signature, $secret, $header->alg)) {
            throw new Exception('Signature verification failed');
        }

        if (isset($payload->nbf) && $payload->nbf > (time() + $this->leeway)) {
            throw new Exception('Cannot handle token prior to '.date(\DateTime::ISO8601, $payload->nbf));
        }

        if (isset($payload->iat) && $payload->iat > (time() + $this->leeway)) {
            throw new Exception('Cannot handle token prior to '.date(\DateTime::ISO8601, $payload->iat));
        }

        if (isset($payload->exp) && (time() - $this->leeway) >= $payload->exp) {
            throw new Exception('Expired token');
        }

        return $payload;
    }

    /**
     * Buat signature.
     *
     * @param string $message
     * @param string $secret
     * @param string $algorithm
     *
     * @return string
     */
    private function signature($message, $secret, $algorithm)
    {
        if (!isset($this->algorithms[$algorithm])) {
            throw new Exception('Algorithm not supported: '.$algorithm);
        }

        return hash_hmac($this->algorithms[$algorithm], $message, $secret, true);
    }

    /**
     * Verifikasi signature dengan message dan secret key.
     *
     * @param string $message
     * @param string $signature
     * @param string $secret
     * @param string $algorithm
     *
     * @return bool
     */
    private function verify($message, $signature, $secret, $algorithm)
    {
        if (empty($this->algorithms[$algorithm])) {
            throw new Exception('Algorithm not supported: '.$algorithm);
        }

        $hash = hash_hmac($this->algorithms[$algorithm], $message, $secret, true);

        return hash_equals($signature, $hash);
    }

    /**
     * Base64 encode url.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function urlSafeBase64Encode($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    /**
     * Base64 decode url.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function urlSafeBase64Decode($data)
    {
        $remainder = mb_strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Json encode data.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function jsonEncode($data)
    {
        $json = json_encode($data);

        if (JSON_ERROR_NONE !== json_last_error_msg()) {
            throw new Exception(json_last_error_msg());
        } elseif ('null' === $json && null !== $data) {
            throw new Exception('Null result with non-null input');
        }

        return $json;
    }

    /**
     * Json decode data.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function jsonDecode($data)
    {
        if (!defined('JSON_C_VERSION') || PHP_INT_SIZE <= 4) {
            $obj = json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $maxInt = mb_strlen((string) PHP_INT_MAX) - 1;
            $obj = json_decode(preg_replace('/:\s*(-?\d{'.$maxInt.',})/', ': "$1"', $data));
        }

        if (JSON_ERROR_NONE !== json_last_error_msg()) {
            throw new Exception(json_last_error_msg());
        } elseif (null === $obj && 'null' !== $data) {
            throw new Exception('Null result with non-null input');
        }

        return $obj;
    }
}
