<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

class Curl
{
    private $ch = null;
    private $error = '';

    protected $followRedirects = true;
    protected $options = [];
    protected $headers = [];
    protected $referrer = null;
    protected $useCookie = false;
    protected $cookieFile = '';
    protected $userAgent = '';
    protected $responseBody = '';
    protected $responseHeader = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->useCookie) {
            $this->cookieFile = storage_path('system/cache/curl_cookie.cache');
        }

        if ('' === $this->userAgent) {
            $this->userAgent = isset($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : 'Hexazor/PHP '.PHP_VERSION.' ('.$_SERVER['HTTP_HOST'].')';
        }
    }

    /**
     * Jalankan HEAD request.
     *
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    public function head($url, array $params = [])
    {
        $this->request('HEAD', $url, $params);
    }

    /**
     * Jalankan GET request.
     *
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    public function get($url, array $params = [])
    {
        if (!empty($params)) {
            $url .= (false !== stripos($url, '?')) ? '&' : '?';
            $url .= is_string($params) ? $params : http_build_query($params, '', '&');
        }

        $this->request('GET', $url);
    }

    /**
     * Jalankan POST request.
     *
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    public function post($url, array $params = [])
    {
        $this->request('POST', $url, $params);
    }

    /**
     * Jalankan PUT request.
     *
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    public function put($url, array $params = [])
    {
        $this->request('PUT', $url, $params);
    }

    /**
     * Jalankan DELETE request.
     *
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    public function delete($url, array $params = [])
    {
        $this->request('DELETE', $url, $params);
    }

    /**
     * Me-return response header.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getResponseHeader($key = null)
    {
        if (is_null($key)) {
            return $this->responseHeader;
        }

        if (array_key_exists($key, $this->responseHeader)) {
            return $this->responseHeader[$key];
        }
    }

    /**
     * Me-return response body.
     *
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * Set user agent untuk request.
     *
     * @param string $agent
     */
    public function setUserAgent($agent)
    {
        return $this->userAgent = $agent;
    }

    /**
     * Set http referrer untuk request.
     *
     * @param string $referrer
     */
    public function setReferrer($referrer)
    {
        return $this->referrer = $referrer;
    }

    /**
     * Set header untuk request.
     *
     * @param string|array $header
     * @param string       $value
     */
    public function setHeader($header, $value = null)
    {
        if (is_array($header)) {
            $this->headers = $header;
        } else {
            $this->headers[$header] = $value;
        }

        return $this->headers;
    }

    /**
     * Set opsi CURL.
     *
     * @param string|array $options
     * @param string       $value
     */
    public function setOptions($options, $value = null)
    {
        if (is_array($options)) {
            $this->options = $options;
        } else {
            $this->options[$options] = $value;
        }

        return $this->options;
    }

    /**
     * Buat CURL request.
     *
     * @param string $method
     * @param string $url
     * @param array  $params
     *
     * @return void
     */
    private function request($method, $url, array $params = [])
    {
        $this->error = '';
        $this->ch = curl_init();

        if (is_array($params)) {
            $params = http_build_query($params, '', '&');
        }

        $this->setRequestMethod($method);
        $this->setRequestOptions($url, $params);
        $this->setRequestHeaders();

        $response = curl_exec($this->ch);

        if ($response) {
            $response = $this->getResponse($response);
        } else {
            $this->error = curl_errno($this->ch).' - '.curl_error($this->ch);
        }

        curl_close($this->ch);
    }

    /**
     * Helper untuk set CURL header.
     */
    private function setRequestHeaders()
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Helper untuk set CURL method.
     *
     * @param string $method
     */
    private function setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->ch, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Helper untuk set CURL option.
     *
     * @param string $url
     * @param array  $params
     */
    private function setRequestOptions($url, $params)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $params = (array) $params;

        if (!empty($params)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);

        if (false !== $this->useCookie) {
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }

        if ($this->followRedirects) {
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        }

        if (null !== $this->referrer) {
            curl_setopt($this->ch, CURLOPT_REFERER, $this->referrer);
        }

        foreach ($this->options as $option => $value) {
            $constant = constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option)));
            curl_setopt($this->ch, $constant, $value);
        }
    }

    /**
     * Ambil response hasil request.
     *
     * @param string $response
     *
     * @return void
     */
    private function getResponse($response)
    {
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        preg_match_all($pattern, $response, $matches);
        $headersStr = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headersStr));

        $this->responseBody = str_replace($headersStr, '', $response);
        $versionAndStatus = array_shift($headers);

        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $versionAndStatus, $matches);
        $this->responseHeader['Http-Version'] = $matches[1];
        $this->responseHeader['Status-Code'] = $matches[2];
        $this->responseHeader['Status'] = $matches[2].' '.$matches[3];

        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->responseHeader[$matches[1]] = $matches[2];
        }
    }

    /**
     * Ambil pesan error.
     *
     * @return string
     */
    public function errors()
    {
        return $this->error;
    }
}
