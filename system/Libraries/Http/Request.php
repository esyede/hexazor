<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

class Request
{
    protected $get;
    protected $post;
    protected $cookie;
    protected $files;
    protected $server;
    protected $globals;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->populateGlobalVariables();
    }

    /**
     * Buat global variables.
     *
     * @return void
     */
    private function populateGlobalVariables()
    {
        foreach ($GLOBALS as $key => $value) {
            switch ($key) {
                case '_GET':    $this->get = $value; break;
                case '_POST':   $this->post = $value; break;
                case '_COOKIE': $this->cookie = $value; break;
                case '_FILES':  $this->files = $value; break;
                case '_SERVER': $this->server = $value; break;
                case 'GLOBALS': $this->globals = $value; break;
            }
        }
    }

    /**
     * Ambil variabel server.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->server;
        }

        if (isset($this->server[$key])) {
            return $this->server[$key];
        }

        return $default;
    }

    /**
     * Ambil http headers.
     *
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function headers($key = null, $default = null)
    {
        $headers = getallheaders();

        if (is_null($key)) {
            return $headers;
        }

        $items = [];
        foreach ($headers as $key => $val) {
            $items[$key] = $val;
        }

        if (isset($items[ucwords($key)])) {
            return $items[ucwords($key)];
        }

        return $default;
    }

    /**
     * Ambil semua value dari variabel global REQUEST.
     *
     * @param bool $filter
     *
     * @return mixed
     */
    public function all($filter = true)
    {
        if ($filter) {
            return $this->filter($_REQUEST);
        }

        return $_REQUEST;
    }

    /**
     * Ambil value berdasarkan key yang diberikan.
     *
     * @param array $keys
     * @param bool  $filter
     *
     * @return mixed
     */
    public function only($keys, $filter = true)
    {
        $keys = array_wrap($keys);
        $array = $this->all($filter);

        return array_only($array, $keys);
    }

    /**
     * Ambil value selain key yang diberikan.
     *
     * @param array $keys
     * @param bool  $filter
     *
     * @return mixed
     */
    public function except($keys, $filter = true)
    {
        $keys = array_wrap($keys);
        $array = $this->all($filter);

        return array_except($array, $keys);
    }

    /**
     * Cek apakah key ada dalam input request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_has($this->all(), $key);
    }

    /**
     * Ambil value dari variable global GET.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function get($key = null, $default = null, $filter = true)
    {
        if (is_null($key)) {
            if ($filter) {
                return $this->filter($this->get);
            }

            return $this->get;
        }

        if (isset($this->get[$key])) {
            if ($filter) {
                return $this->filter($this->get[$key]);
            }

            return $this->get[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global POST.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function post($key = null, $default = null, $filter = true)
    {
        if (is_null($key)) {
            if ($filter) {
                return $this->filter($this->pust);
            }

            return $this->pust;
        }

        if (isset($this->post[$key])) {
            if ($filter) {
                return $this->filter($this->post[$key]);
            }

            return $this->post[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global PUT.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function put($key = null, $default = null, $filter = true)
    {
        $_PUT = [];
        parse_str(file_get_contents('php://input'), $_PUT);

        if (is_null($key)) {
            if ($filter) {
                return $this->filter($_PUT);
            }

            return $_PUT;
        }

        if (isset($_PUT[$key])) {
            if ($filter) {
                return $this->filter($_PUT[$key]);
            }

            return $_PUT[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global PATCH.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function patch($key = null, $default = null, $filter = true)
    {
        $_PATCH = [];
        parse_str(file_get_contents('php://input'), $_PATCH);

        if (is_null($key)) {
            if ($filter) {
                return $this->filter($_PATCH);
            }

            return $_PATCH;
        }

        if (isset($_PATCH[$key])) {
            if ($filter) {
                return $this->filter($_PATCH[$key]);
            }

            return $_PATCH[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global DELETE.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function delete($key = null, $default = null, $filter = true)
    {
        $_DELETE = [];
        parse_str(file_get_contents('php://input'), $_DELETE);

        if (is_null($key)) {
            if ($filter) {
                return $this->filter($_DELETE);
            }

            return $_DELETE;
        }

        if (isset($_DELETE[$key])) {
            if ($filter) {
                return $this->filter($_DELETE[$key]);
            }

            return $_DELETE[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global COOKIE.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function cookie($key = null, $default = null, $filter = true)
    {
        if (is_null($key)) {
            if ($filter) {
                return $this->filter($this->cookie);
            }

            return $this->cookie;
        }

        if (isset($this->cookie[$key])) {
            if ($filter) {
                return $this->filter($this->cookie[$key]);
            }

            return $this->cookie[$key];
        }

        return $default;
    }

    /**
     * Ambil value dari variable global FILES.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function files($key = null)
    {
        if (is_null($key)) {
            if ($filter) {
                return $this->filter($this->files);
            }

            return $this->files;
        }

        if (isset($this->files[$key])) {
            if ($filter) {
                return $this->filter($this->files[$key]);
            }

            return $this->files[$key];
        }

        return $default;
    }

    /**
     * Ambil data dari GLOBALS request.
     *
     * @param string|null $key
     * @param mixed       $default
     * @param bool        $filter
     *
     * @return mixed
     */
    public function globals($key = null, $default = null, $filter = true)
    {
        if (is_null($key)) {
            if ($filter) {
                return $this->filter($this->globals);
            }

            return $this->globals;
        }

        if (isset($this->globals[$key])) {
            if ($filter) {
                return $this->filter($this->globals[$key]);
            }

            return $this->globals[$key];
        }

        return $default;
    }

    /**
     * Ambil tipe (method) request.
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->server('REQUEST_METHOD');
    }

    /**
     * Ambil server script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server('SCRIPT_NAME');
    }

    /**
     * Ambil server path info.
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->server('PATH_INFO');
    }

    /**
     * Ambil request scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return true === stripos($this->server('SERVER_PROTOCOL'), 'https') ? 'https' : 'http';
    }

    /**
     * Ambil http host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * Ambil request URI.
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->server('REQUEST_URI');
    }

    /**
     * Ambil base URL.
     *
     * @param string $url
     *
     * @return string
     */
    public function baseUrl($url = null)
    {
        if (is_null($url)) {
            return $this->getScheme().'://'.$this->getHost();
        }

        return $this->getScheme().'://'.rtrim($this->getHost(), '/').'/'.$url;
    }

    /**
     * Ambil segment - segment url.
     *
     * @return array
     */
    public function segments()
    {
        return explode('/', trim(parse_url($this->getRequestUri(), PHP_URL_PATH), '/'));
    }

    /**
     * Ambil salah satu segment url.
     *
     * @param int $index
     *
     * @return string
     */
    public function getSegment($index)
    {
        return isset($this->segments()[$index]) ? $this->segments()[$index] : null;
    }

    /**
     * mbil segment url saat ini.
     *
     * @return string
     */
    public function currentSegment()
    {
        return $this->getSegment(count($this->segments()) - 1);
    }

    /**
     * AAmbil query string.
     *
     * @param bool $asArray
     *
     * @return string|array
     */
    public function getQueryString($asArray = false)
    {
        if (false === $asArray) {
            return $this->server('QUERY_STRING');
        }

        $qsParts = explode('&', $this->server('QUERY_STRING'));

        $qsArray = [];
        foreach ($qsParts as $key => $value) {
            $qsItems = explode('=', $value);
            $qsArray[$qsItems[0]] = $qsItems[1];
        }

        return $qsArray;
    }

    /**
     * Ambil http content-type.
     *
     * @return array
     */
    public function getContentType()
    {
        return explode(',', $this->headers()['Accept'])[0];
    }

    /**
     * Ambil http-accept language.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getLocales()[0];
    }

    /**
     * Ambil list http-accept language.
     *
     * @return array
     */
    public function getLocales()
    {
        $locales = strtolower(trim($this->server('HTTP_ACCEPT_LANGUAGE')));
        $locales = preg_replace('/(;q=[0-9\.]+)/i', '', $locales);

        return explode(',', $locales);
    }

    /**
     * Cek apakah method saat ini cocok.
     *
     * @param atring $method
     *
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getRequestMethod() === strtoupper($method);
    }

    /**
     * Cek apakah method saat ini adalah GET atau bukan.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('get');
    }

    /**
     * Cek apakah method saat ini adalah POST atau bukan.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('post');
    }

    /**
     * Cek apakah method saat ini adalah PUT atau bukan.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('put');
    }

    /**
     * Cek apakah method saat ini adalah PATCH atau bukan.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('patch');
    }

    /**
     * Cek apakah method saat ini adalah DELETE atau bukan.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('delete');
    }

    /**
     * Cek apakah method saat ini adalah HEAD atau bukan.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod('head');
    }

    /**
     * Cek apakah method saat ini adalah OPTIONS atau bukan.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod('options');
    }

    /**
     * Cek apakah request saat ini merupakan ajax request.
     *
     * @return bool
     */
    public function isAjax()
    {
        return filled($this->server('HTTP_X_REQUESTED_WITH'))
            && 'xmlhttprequest' === strtolower($this->server('HTTP_X_REQUESTED_WITH'));
    }

    /**
     * Cek apakah request menggunakan https.
     *
     * @return bool
     */
    public function isSecure()
    {
        if (null !== $this->server('HTTPS')) {
            return true;
        }

        if (null !== $this->server('HTTP_X_FORWARDED_PROTO')
        && 'https' === $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah request diminta oleh bot (di cek via user-agent).
     *
     * @return bool
     */
    public function isRobot()
    {
        return (null !== $this->getUserAgent())
            && preg_match('/bot|crawl|slurp|spider/i', $this->server('HTTP_USER_AGENT'));
    }

    /**
     * Cek apakah sequest diminta melalui mobile browser (di cek via user-agent).
     *
     * @return bool
     */
    public function isMobile()
    {
        $pattern = '/(android|avantgo|blackberry|bolt|boost|'.
            'cricket|docomo|fone|hiptop|mini|mobi|'.
            'palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i';

        return preg_match($pattern, $this->getUserAgent());
    }

    /**
     * Cek apakah request diminta via ip proxy.
     *
     * @return bool
     */
    public function isReferral()
    {
        if (null !== $this->server('HTTP_REFERER')
        || '' === $this->server('HTTP_REFERER')) {
            return false;
        }

        return true;
    }

    /**
     * Ambil ip proxy pengunjung.
     *
     * @return string
     */
    public function getReferrer()
    {
        return ($this->isReferral()) ? trim($this->server('HTTP_REFERER')) : '';
    }

    /**
     * Ambil ip pengunjung.
     *
     * @return string
     */
    public function getIp()
    {
        $ip = getenv('HTTP_CLIENT_IP')
            ?: getenv('HTTP_X_FORWARDED_FOR')
                ?: getenv('HTTP_X_FORWARDED')
                    ?: getenv('HTTP_FORWARDED_FOR')
                        ?: getenv('HTTP_FORWARDED')
                            ?: getenv('REMOTE_ADDR');

        return (false === $ip) ? 'UNKNOWN' : $ip;
    }

    /**
     * Ambil user-agent pengunjung.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * Filter data dari serangan XSS.
     *
     * @param string|array $data
     *
     * @return string|array
     */
    public function filter($data = null)
    {
        $this->trimData($data);

        if (is_null($data)) {
            return;
        }

        if (is_array($data)) {
            return array_map([$this, 'xssClean'], $data);
        }

        return $this->xssClean($data);
    }

    /**
     * Trim data inputan.
     *
     * @param string|array $data
     *
     * @return string|array
     */
    public function trimData($data = null)
    {
        if (is_null($data)) {
            return;
        }

        if (is_array($data)) {
            return array_map('trim', $data);
        }

        return trim($data);
    }

    /**
     * Method untuk filter serangan XSS.
     *
     * @param string|array $data
     *
     * @return string
     */
    public function xssClean($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                $data[$key] = $this->xssClean($value);
            }

            return $data;
        }

        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*'.
            'j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*'.
            's[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...',
            $data
        );

        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*'.
            's[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...',
            $data
        );

        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...',
            $data
        );

        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?'.
            'expression[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );

        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?'.
            'behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );

        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?'.
            's[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*'.
            'p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>',
            $data
        );

        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            $old = $data;
            $data = preg_replace(
                '#</*(?:applet|b(?:ase|gsound|link)'.
                '|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)'.
                '|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '',
                $data
            );
        } while ($old !== $data);

        return $data;
    }

    /**
     * Method untuk filter string html.
     *
     * @param string $data
     *
     * @return string
     */
    public function htmlClean($data)
    {
        $this->trimData($data);

        $htmlClean = function ($string) {
            return strip_tags(htmlentities(
                stripslashes($string),
                ENT_NOQUOTES,
                'UTF-8'
            ));
        };

        if (is_array($data)) {
            return array_map($htmlClean, $data);
        }

        return $htmlClean($data);
    }
}
