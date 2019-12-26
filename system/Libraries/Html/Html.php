<?php

namespace System\Libraries\Html;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;

class Html
{
    public static $extensions = [];

    /**
     * Daftarkan custom method.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return void
     */
    public static function extend($name, Closure $callback)
    {
        static::$extensions[$name] = $callback;
    }

    /**
     * Saring dengan htmlentities().
     *
     * @param string $value
     *
     * @return string
     */
    public static function entities($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Decode dengan html_entity_decode().
     *
     * @param string $value
     *
     * @return string
     */
    public static function decode($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Saring dengan htmlspecialchars().
     *
     * @param string $value
     *
     * @return string
     */
    public static function specialchars($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Tambahkan tag script (javascript).
     *
     * @param string $url
     * @param array  $attributes
     *
     * @return string
     */
    public static function script($url, $attributes = [])
    {
        return '<script src="'.$url.'"'.static::attributes($attributes).'></script>'.PHP_EOL;
    }

    /**
     * Tambahkan tag style (css).
     *
     * @param string $url
     * @param array  $attributes
     *
     * @return string
     */
    public static function style($url, $attributes = [])
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];
        $attributes = $attributes + $defaults;

        return '<link href="'.$url.'"'.static::attributes($attributes).'>'.PHP_EOL;
    }

    /**
     * Tambahkan tag span.
     *
     * @param string $value
     * @param array  $attributes
     *
     * @return string
     */
    public static function span($value, $attributes = [])
    {
        $html = '<span'.static::attributes($attributes).'>';
        $html .= static::entities($value);
        $html .= '</span>'.PHP_EOL;

        return $htnl;
    }

    /**
     * Tambahkan tah link href.
     *
     * @param string $url
     * @param string $title
     * @param array  $attributes
     *
     * @return string
     */
    public static function link($url, $title = null, $attributes = [])
    {
        $title = is_null($title) ? $url : $title;

        $html = '<a href="'.$url.'"'.static::attributes($attributes).'>';
        $html .= static::entities($title).'</a>';

        return $html;
    }

    /**
     * Tambahkan tag mailto.
     *
     * @param string $email
     * @param string $title
     * @param array  $attributes
     *
     * @return string
     */
    public static function mailto($email, $title = null, $attributes = [])
    {
        $email = static::email($email);
        $title = is_null($title) ? $email : $title;
        $email = '&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email;

        $html = '<a href="'.$email.'"'.static::attributes($attributes).'>';
        $html .= static::entities($title).'</a>'.PHP_EOL;

        return $html;
    }

    /**
     * Obfuscate email untuk mencegah bot spam.
     *
     * @param string $email
     *
     * @return string
     */
    public static function email($email)
    {
        return str_replace('@', '&#64;', static::obfuscate($email));
    }

    /**
     * Tambahkan tag image.
     *
     * @param string $url
     * @param string $alt
     * @param array  $attributes
     *
     * @return string
     */
    public static function image($url, $alt = '', $attributes = [])
    {
        $attributes['alt'] = $alt;

        return '<img src="'.$url.'"'.static::attributes($attributes).'>'.PHP_EOL;
    }

    /**
     * Tambahkan tag ordered list.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string
     */
    public static function ol($list, $attributes = [])
    {
        return static::listing('ol', $list, $attributes);
    }

    /**
     * Tambahkan tag un-ordered-list.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string
     */
    public static function ul($list, $attributes = [])
    {
        return static::listing('ul', $list, $attributes);
    }

    /**
     * Proses ordered / un-ordered list.
     *
     * @param string $type
     * @param array  $list
     * @param array  $attributes
     *
     * @return string
     */
    private static function listing($type, $list, $attributes = [])
    {
        $html = '';
        if (0 == count($list)) {
            return $html;
        }

        foreach ($list as $key => $value) {
            if (is_array($value)) {
                if (is_int($key)) {
                    $html .= static::listing($type, $value);
                } else {
                    $html .= '<li>'.$key.static::listing($type, $value).'</li>'.PHP_EOL;
                }
            } else {
                $html .= '<li>'.static::entities($value).'</li>'.PHP_EOL;
            }
        }

        return '<'.$type.static::attributes($attributes).'>'.PHP_EOL.$html.'</'.$type.'>';
    }

    /**
     * Tambahkan tag definition list.
     *
     * @param array $list
     * @param array $attributes
     *
     * @return string
     */
    public static function dl($list, $attributes = [])
    {
        $html = '';
        if (0 == count($list)) {
            return $html;
        }

        foreach ($list as $term => $description) {
            $html .= '<dt>'.static::entities($term).'</dt>'.PHP_EOL;
            $html .= '<dd>'.static::entities($description).'</dd>'.PHP_EOL;
        }

        return '<dl'.static::attributes($attributes).'>'.PHP_EOL.$html.'</dl>';
    }

    /**
     * Bangun daftar attribute html dari sebuah array.
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function attributes($attributes)
    {
        $attributes = (array) $attributes;
        $html = [];
        foreach ($attributes as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }

            if (!is_null($value)) {
                $html[] = $key.'="'.static::entities($value).'"';
            }
        }

        return (count($html) > 0) ? ' '.implode(' ', $html) : '';
    }

    /**
     * Obfuscate string.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function obfuscate($value)
    {
        $safe = '';
        foreach (str_split($value) as $letter) {
            switch (rand(1, 3)) {
                case 1: $safe .= '&#'.ord($letter).';'; break;
                case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
                case 3: $safe .= $letter;
            }
        }

        return $safe;
    }

    /**
     * Tangani pemanggilan custom function (extend).
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (isset(static::$extensions[$method])) {
            return call_user_func_array(static::$extensions[$method], $parameters);
        }

        throw new Exception("Method [$method] does not exist.");
    }
}
