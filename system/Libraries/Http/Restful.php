<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;
use System\Facades\Curl;

class Restful
{
    /**
     * Kirim GET request.
     *
     * @param string $url
     * @param array  $data
     */
    public function get($url, array $data = [])
    {
        $this->validateRequiredParameter($url, 'URL');
        Curl::get($url, $data);
    }

    /**
     * Kirim POST request.
     *
     * @param string $url
     * @param array  $data
     */
    public function post($url, array $data = [])
    {
        $this->validateRequiredParameter($url, 'URL');
        Curl::post($url, $data);
    }

    /**
     * Kirim PUT request.
     *
     * @param string $url
     * @param array  $data
     */
    public function put($url, array $data = [])
    {
        $this->validateRequiredParameter($url, 'URL');
        Curl::put($url, $data);
    }

    /**
     * Kirim DELETE request.
     *
     * @param string $url
     * @param array  $data
     */
    public function delete($url, array $data = [])
    {
        $this->validateRequiredParameter($url, 'URL');
        Curl::delete($url, $data);
    }

    /**
     * Set header untuk request.
     *
     * @param string $header
     * @param mixed  $value
     */
    public function setHeader($header, $value = null)
    {
        $this->validateRequiredParameter($header, 'Header');
        Curl::setHeader($header, $value);

        return $this;
    }

    /**
     * Ambil response hasil request.
     *
     * @return string
     */
    public function response()
    {
        return Curl::getResponseBody();
    }

    /**
     * Ambil status code hasil request.
     *
     * @return int
     */
    public function statusCode()
    {
        return Curl::getResponseHeader('Status-Code');
    }

    /**
     * Validasi parameter method tidak boleh kosong.
     *
     * @param string $param
     * @param string $alias
     */
    private function validateRequiredParameter($param, $alias)
    {
        if (blank($param)) {
            throw new InvalidArgumentException("The [$alias] parameter cannot be empty.");
        }
    }
}
