<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

use Exception;

class Response
{
    protected $sent = false;
    protected $status = 200;
    protected $headers = [];
    protected $body;

    public static $codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',

        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',

        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',

        426 => 'Upgrade Required',

        428 => 'Precondition Required',
        429 => 'Too Many Requests',

        431 => 'Request Header Fields Too Large',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',

        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Set HTTP status code.
     *
     * @param int $code
     *
     * @return $this|int
     */
    public function status($code = null)
    {
        if (null === $code) {
            return $this->status;
        }

        if (array_key_exists($code, self::$codes)) {
            $this->status = $code;
        } else {
            throw new Exception('Invalid HTTP status code: '.$code);
        }

        return $this;
    }

    /**
     * Tambahkan header ke response.
     *
     * @param string|array $name
     * @param string       $value
     *
     * @return $this
     */
    public function header($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->headers[$k] = $v;
            }
        } else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Me-return the header - header dari response.
     *
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Tulis konten ke response body.
     *
     * @param string $str
     *
     * @return $this
     */
    public function body($str)
    {
        $this->body .= $str;

        return $this;
    }

    /**
     * Bersihkan (reset) response.
     *
     * @return $this
     */
    public function clear()
    {
        $this->status = 200;
        $this->headers = [];
        $this->body = '';

        return $this;
    }

    /**
     * Set caching header untuk response.
     *
     * @param int|string $expires
     *
     * @return $this
     */
    public function cache($expires)
    {
        if (false === $expires) {
            $this->headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $this->headers['Cache-Control'] = [
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0',
            ];

            $this->headers['Pragma'] = 'no-cache';
        } else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->headers['Expires'] = gmdate('D, d M Y H:i:s', $expires).' GMT';
            $this->headers['Cache-Control'] = 'max-age='.($expires - time());

            if (isset($this->headers['Pragma']) && 'no-cache' == $this->headers['Pragma']) {
                unset($this->headers['Pragma']);
            }
        }

        return $this;
    }

    /**
     * Kirim HTTP headers.
     *
     * @return $this
     */
    public function sendHeaders()
    {
        // Kirim header status code
        if (false !== strpos(PHP_SAPI, 'cgi')) {
            header(sprintf('Status: %d %s', $this->status, self::$codes[$this->status]), true);
        } else {
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            $status = $this->status;
            header(sprintf('%s %d %s', $protocol, $status, self::$codes[$status]), true, $status);
        }

        // Kirim header lainnya
        foreach ($this->headers as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header($field.': '.$v, false);
                }
            } else {
                header($field.': '.$value);
            }
        }

        $length = $this->getContentLength();

        if ($length > 0) {
            header('Content-Length: '.$length);
        }

        return $this;
    }

    /**
     * Ambil value header content length.
     *
     * @return int
     */
    public function getContentLength()
    {
        return mb_strlen($this->body, 'latin1');
    }

    /**
     * Cek responsenya terkirim atau tidak.
     *
     * @return bool
     */
    public function sent()
    {
        return $this->sent;
    }

    /**
     * Kirim HTTP response.
     *
     * @return void
     */
    public function send()
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            $this->sendHeaders();
        }

        echo $this->body;
        $this->sent = true;
    }

    /**
     * Kirim response json.
     *
     * @param mixed $data
     * @param int   $code
     * @param int   $option
     */
    public function json($data, $code = 200, $option = 0)
    {
        $json = json_encode($data, $option);

        $this->status($code)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->body($json)
            ->send();
    }
}
