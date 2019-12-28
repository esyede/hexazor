<?php

defined('DS') or exit('No direct script access allowed.');
/*
 * Polyfill untuk fitur baru PHP yang diperkenalkan setelah rilis 5.4.0 (versi adaptasi)
 *
 * Credits:
 * Symfony polyfill project          (https://github.com/symfony/polyfill)
 * Anthony Ferrara's password compat (https://github.com/ircmaxell/password_compat)
 * Paragon IE's random-compat        (https://github.com/paragonie/random_compat)
 */

// ---------------------------------------------------------------------
// Konstanta baru (menggunakan default value php 7.3.2 di win 7 32bit)
// ---------------------------------------------------------------------
defined('PHP_INT_MIN') or define('PHP_INT_MIN', ~PHP_INT_MAX);
defined('PHP_FLOAT_MIN') or define('PHP_FLOAT_MIN', 2.2250738585072014e-308);
defined('PHP_FLOAT_MAX') or define('PHP_FLOAT_MAX', 1.7976931348623157e+308);
defined('PHP_FLOAT_EPSILON') or define('PHP_FLOAT_EPSILON', 2.220446049250313e-16);
defined('PHP_FLOAT_DIG') or define('PHP_FLOAT_DIG', 15);
defined('PHP_FD_SETSIZE') or define('PHP_FD_SETSIZE', 256);

// ---------------------------------------------------------------------
// Kelas exception dan error handler baru
// ---------------------------------------------------------------------
if (!class_exists('JsonException', false)) {
    class JsonException extends \Exception
    {
    }
}

if (!class_exists('Error', false)) {
    class Error extends \Exception
    {
    }
}

if (!class_exists('AssertionError', false)) {
    class AssertionError extends Error
    {
    }
}

if (!class_exists('ParseError', false)) {
    class ParseError extends Error
    {
    }
}

if (!class_exists('TypeError', false)) {
    if (is_subclass_of('Error', 'Exception')) {
        class TypeError extends Error
        {
        }
    } else {
        class TypeError extends \Exception
        {
        }
    }
}

if (!class_exists('ArgumentCountError', false)) {
    class ArgumentCountError extends TypeError
    {
    }
}

if (!class_exists('ArithmeticError', false)) {
    class ArithmeticError extends Error
    {
    }
}

if (!class_exists('DivisionByZeroError', false)) {
    class DivisionByZeroError extends ArithmeticError
    {
    }
}

// ---------------------------------------------------------------------
// getallheaders
// ---------------------------------------------------------------------
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];

        $server = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($server[$key])) {
                $headers[$server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $pwd = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic '.base64_encode($_SERVER['PHP_AUTH_USER'].':'.$pwd);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}

// ---------------------------------------------------------------------
// PHP 5.5.0+
// ---------------------------------------------------------------------
if (!function_exists('boolval')) {
    function boolval($val)
    {
        return (bool) $val;
    }
}

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE: return 'No error';
            case JSON_ERROR_DEPTH: return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH: return 'State mismatch (invalid or malformed JSON)';
            case JSON_ERROR_CTRL_CHAR: return 'Control character error, possibly incorrectly encoded';
            case JSON_ERROR_SYNTAX: return 'Syntax error';
            case JSON_ERROR_UTF8: return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default: return 'Unknown error';
        }
    }
}

if (!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $output = [];
        foreach ($input as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;

            if (null !== $indexKey && array_key_exists($indexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$indexKey];
            }

            if (null === $columnKey) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($columnKey, $row)) {
                $valueSet = true;
                $value = $row[$columnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $output[$key] = $value;
                } else {
                    $output[] = $value;
                }
            }
        }

        return $output;
    }
}

if (!function_exists('hash_pbkdf2')) {
    function hash_pbkdf2($algorithm, $password, $salt, $iterations, $length = 0, $rawOutput = false)
    {
        $hashLength = strlen(hash($algorithm, '', true));
        switch ($algorithm) {
            case 'sha1':
            case 'sha224':
            case 'sha256':
                $blockSize = 64;
                break;
            case 'sha384':
            case 'sha512':
                $blockSize = 128;
                break;
            default:
                $blockSize = $hashLength;
                break;
        }
        if ($length < 1) {
            $length = $hashLength;
            if (!$rawOutput) {
                $length <<= 1;
            }
        }

        $blocks = ceil($length / $hashLength);
        $digest = '';
        if (strlen($password) > $blockSize) {
            $password = hash($algorithm, $password, true);
        }

        for ($i = 1; $i <= $blocks; $i++) {
            $ib = $block = hash_hmac($algorithm, $salt.pack('N', $i), $password, true);

            for ($j = 1; $j < $iterations; $j++) {
                $ib ^= ($block = hash_hmac($algorithm, $block, $password, true));
            }

            $digest .= $ib;
        }

        if (!$rawOutput) {
            $digest = bin2hex($digest);
        }

        return substr($digest, 0, $length);
    }
}

// Library password compat (password_hash, password_verify dll.)
// Fetched: December 28, 2019
require_once __DIR__.'/compat/password/password.php';

// ---------------------------------------------------------------------
// PHP 5.6.0+
// ---------------------------------------------------------------------
if (!function_exists('ldap_escape')) {
    defined('LDAP_ESCAPE_FILTER') or define('LDAP_ESCAPE_FILTER', 1);
    defined('LDAP_ESCAPE_DN') or define('LDAP_ESCAPE_DN', 2);

    function ldap_escape($subject, $ignore = '', $flags = 0)
    {
        static $_polyfillCharMaps = null;

        if (null === $_polyfillCharMaps) {
            $_polyfillCharMaps = [
                LDAP_ESCAPE_FILTER => ['\\', '*', '(', ')', "\x00"],
                LDAP_ESCAPE_DN     => ['\\', ',', '=', '+', '<', '>', ';', '"', '#', "\r"],
            ];

            $_polyfillCharMaps[0] = [];
            for ($i = 0; $i < 256; $i++) {
                $_polyfillCharMaps[0][chr($i)] = sprintf('\\%02x', $i);
            }

            for ($i = 0, $l = count($_polyfillCharMaps[LDAP_ESCAPE_FILTER]); $i < $l; $i++) {
                $chr = $_polyfillCharMaps[LDAP_ESCAPE_FILTER][$i];
                unset($_polyfillCharMaps[LDAP_ESCAPE_FILTER][$i]);
                $_polyfillCharMaps[LDAP_ESCAPE_FILTER][$chr] = $_polyfillCharMaps[0][$chr];
            }

            for ($i = 0, $l = count($_polyfillCharMaps[LDAP_ESCAPE_DN]); $i < $l; $i++) {
                $chr = $_polyfillCharMaps[LDAP_ESCAPE_DN][$i];
                unset($_polyfillCharMaps[LDAP_ESCAPE_DN][$i]);
                $_polyfillCharMaps[LDAP_ESCAPE_DN][$chr] = $_polyfillCharMaps[0][$chr];
            }
        }

        $flags = (int) $flags;
        $charMap = [];

        if ($flags & LDAP_ESCAPE_FILTER) {
            $charMap += $_polyfillCharMaps[LDAP_ESCAPE_FILTER];
        }

        if ($flags & LDAP_ESCAPE_DN) {
            $charMap += $_polyfillCharMaps[LDAP_ESCAPE_DN];
        }

        if (!$charMap) {
            $charMap = $_polyfillCharMaps[0];
        }

        $ignore = (string) $ignore;

        for ($i = 0, $l = strlen($ignore); $i < $l; $i++) {
            unset($charMap[$ignore[$i]]);
        }

        $result = strtr($subject, $charMap);

        if ($flags & LDAP_ESCAPE_DN) {
            if (' ' === $result[0]) {
                $result = '\\20'.substr($result, 1);
            }

            if (' ' === $result[strlen($result) - 1]) {
                $result = substr($result, 0, -1).'\\20';
            }
        }

        return $result;
    }
}

if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string)
    {
        if (!is_string($known_string)) {
            trigger_error(
                'Expected $known_string to be a string, '.gettype($known_string).' given',
                E_USER_WARNING
            );

            return false;
        }

        if (!is_string($user_string)) {
            trigger_error(
                'Expected $user_string to be a string, '.gettype($user_string).' given',
                E_USER_WARNING
            );

            return false;
        }

        $knownLen = _polyfill_strlen($known_string);
        $userLen = _polyfill_strlen($user_string);

        if ($knownLen !== $userLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $knownLen; $i++) {
            $result |= ord($known_string[$i]) ^ ord($user_string[$i]);
        }

        return 0 === $result;
    }
}

// ---------------------------------------------------------------------
// PHP 7.0+
// ---------------------------------------------------------------------
// Fungsi bantuan untuk intdiv() dan preg_replace_array()
if (!function_exists('_polyfill_int_arg')) {
    function _polyfill_int_arg($value, $caller, $pos)
    {
        if (is_int($value)) {
            return $value;
        }

        if (!is_numeric($value) || PHP_INT_MAX <= ($value += 0) || PHP_INT_MIN >= $value) {
            $type = gettype($value);
            $message = sprintf('%s() expects parameter %d to be integer, %s given', $caller, $pos, $type);

            throw new \TypeError($message);
        }

        return (int) $value;
    }
}

if (!function_exists('intdiv')) {
    function intdiv($dividend, $divisor)
    {
        $dividend = _polyfill_int_arg($dividend, __FUNCTION__, 1);
        $divisor = _polyfill_int_arg($divisor, __FUNCTION__, 2);

        if (0 === $divisor) {
            throw new \DivisionByZeroError('Division by zero');
        }
        if (-1 === $divisor && PHP_INT_MIN === $dividend) {
            throw new \ArithmeticError('Division of PHP_INT_MIN by -1 is not an integer');
        }

        return ($dividend - ($dividend % $divisor)) / $divisor;
    }
}

if (!function_exists('preg_replace_callback_array')) {
    function preg_replace_callback_array(array $patterns, $subject, $limit = -1, &$count = 0)
    {
        $count = 0;
        $result = (string) $subject;
        if (0 === $limit = _polyfill_int_arg($limit, __FUNCTION__, 3)) {
            return $result;
        }

        foreach ($patterns as $pattern => $callback) {
            $result = preg_replace_callback($pattern, $callback, $result, $limit, $c);
            $count += $c;
        }

        return $result;
    }
}

if (!function_exists('error_clear_last')) {
    function error_clear_last()
    {
        static $__polyfill_handler;

        if (!$__polyfill_handler) {
            $__polyfill_handler = function () {
                return false;
            };
        }

        set_error_handler($__polyfill_handler);
        @trigger_error('');
        restore_error_handler();
    }
}

// Library random compat (random_bytes dan random_int)
// Fetched: December 28, 2019
require_once __DIR__.'/compat/random/random.php';

// ---------------------------------------------------------------------
// PHP 7.1+
// ---------------------------------------------------------------------
if (!function_exists('is_iterable')) {
    function is_iterable($var)
    {
        return is_array($var) || $var instanceof \Traversable;
    }
}

// ---------------------------------------------------------------------
// PHP 7.2+
// ---------------------------------------------------------------------
if (!function_exists('utf8_encode')) {
    function utf8_encode($s)
    {
        $s .= $s;
        $len = strlen($s);

        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
                default: $s[$j] = "\xC3"; $s[++$j] = chr(ord($s[$i]) - 64); break;
            }
        }

        return substr($s, 0, $j);
    }
}

if (!function_exists('utf8_decode')) {
    function utf8_decode($s)
    {
        $s = (string) $s;
        $len = strlen($s);

        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($s[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    $c = (ord($s[$i] & "\x1F") << 6) | ord($s[++$i] & "\x3F");
                    $s[$j] = $c < 256 ? chr($c) : '?';
                    break;

                case "\xF0":
                    ++$i;
                    // Intentionally no break

                    // no break
                case "\xE0":
                    $s[$j] = '?';
                    $i += 2;
                    break;

                default:
                    $s[$j] = $s[$i];
            }
        }

        return substr($s, 0, $j);
    }
}

if (!function_exists('php_os_family')) {
    function php_os_family()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            return 'Windows';
        }

        $map = [
            'Darwin'    => 'Darwin',
            'DragonFly' => 'BSD',
            'FreeBSD'   => 'BSD',
            'NetBSD'    => 'BSD',
            'OpenBSD'   => 'BSD',
            'Linux'     => 'Linux',
            'SunOS'     => 'Solaris',
        ];

        return isset($map[PHP_OS]) ? $map[PHP_OS] : 'Unknown';
    }

    defined('PHP_OS_FAMILY') or define('PHP_OS_FAMILY', php_os_family());
}

if (!function_exists('spl_object_id')) {
    function spl_object_id($object)
    {
        static $__polyfill_hashMask;

        $_obj = (object) [];
        if (null === $__polyfill_hashMask) {
            $__polyfill_hashMask = -1;
            $_obFuncs = [
                'ob_clean', 'ob_end_clean',
                'ob_flush', 'ob_end_flush',
                'ob_get_contents', 'ob_get_flush',
            ];

            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
                if (isset($frame['function'][0])
                && !isset($frame['class'])
                && 'o' === $frame['function'][0]
                && in_array($frame['function'], $_obFuncs)) {
                    $frame['line'] = 0;
                    break;
                }
            }

            if (!empty($frame['line'])) {
                ob_start();
                debug_zval_dump($obj);
                $__polyfill_hashMask = (int) substr(ob_get_clean(), 17);
            }

            $__polyfill_hashMask ^= hexdec(substr(
                spl_object_hash($_obj),
                16 - PHP_INT_SIZE,
                PHP_INT_SIZE
            ));
        }

        if (null === $hash = spl_object_hash($object)) {
            return;
        }

        return $__polyfill_hashMask ^ hexdec(substr($hash, 16 - PHP_INT_SIZE, PHP_INT_SIZE));
    }
}

if (!function_exists('stream_isatty')) {
    function stream_isatty($stream)
    {
        if (!is_resource($stream)) {
            trigger_error(
                'stream_isatty() expects parameter 1 to be resource, '.gettype($stream).' given',
                E_USER_WARNING
            );

            return false;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $stat = @fstat($stream);

            return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
        }

        return function_exists('posix_isatty') && @posix_isatty($stream);
    }
}

if (!function_exists('sapi_windows_vt100_support')) {
    function sapi_windows_vt100_support($stream, $enable = null)
    {
        if (!is_resource($stream)) {
            trigger_error(
                'sapi_windows_vt100_support() expects parameter 1 to be resource, '.
                    gettype($stream).' given',
                E_USER_WARNING
            );

            return false;
        }

        $meta = stream_get_meta_data($stream);
        if ('STDIO' !== $meta['stream_type']) {
            trigger_error(
                'sapi_windows_vt100_support() was not able to analyze the specified stream',
                E_USER_WARNING
            );

            return false;
        }

        if (false === $enable || !stream_isatty($stream)) {
            return false;
        }

        $meta = array_map('strtolower', $meta);
        $stdin = 'php://stdin' === $meta['uri'] || 'php://fd/0' === $meta['uri'];

        return !$stdin
            && (false !== getenv('ANSICON')
            || 'ON' === getenv('ConEmuANSI')
            || 'xterm' === getenv('TERM')
            || 'Hyper' === getenv('TERM_PROGRAM'));
    }
}

if (!function_exists('mb_chr')) {
    function mb_chr($code, $encoding = null)
    {
        if (0x80 > $code %= 0x200000) {
            $s = chr($code);
        } elseif (0x800 > $code) {
            $s = chr(0xC0 | $code >> 6).chr(0x80 | $code & 0x3F);
        } elseif (0x10000 > $code) {
            $s = chr(0xE0 | $code >> 12).chr(0x80 | $code >> 6 & 0x3F).
                chr(0x80 | $code & 0x3F);
        } else {
            $s = chr(0xF0 | $code >> 18).chr(0x80 | $code >> 12 & 0x3F).
                chr(0x80 | $code >> 6 & 0x3F).chr(0x80 | $code & 0x3F);
        }

        if ('UTF-8' !== $encoding) {
            $s = mb_convert_encoding($s, $encoding, 'UTF-8');
        }

        return $s;
    }
}

if (!function_exists('mb_ord')) {
    function mb_ord($s, $encoding = null)
    {
        if (null == $encoding) {
            $s = mb_convert_encoding($s, 'UTF-8');
        } elseif ('UTF-8' !== $encoding) {
            $s = mb_convert_encoding($s, 'UTF-8', $encoding);
        }

        if (1 === strlen($s)) {
            return ord($s);
        }

        $code = ($s = unpack('C*', substr($s, 0, 4))) ? $s[1] : 0;
        if (0xF0 <= $code) {
            return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) +
                (($s[3] - 0x80) << 6) + $s[4] - 0x80;
        }
        if (0xE0 <= $code) {
            return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
        }
        if (0xC0 <= $code) {
            return (($code - 0xC0) << 6) + $s[2] - 0x80;
        }

        return $code;
    }
}

// ---------------------------------------------------------------------
// PHP 7.3+
// ---------------------------------------------------------------------
if (!function_exists('hrtime')) {
    function hrtime($asNum = false)
    {
        $ns = microtime(false);
        $s = substr($ns, 11) - 1533462603;
        $ns = 1E9 * (float) $ns;

        if ($asNum) {
            $ns += $s * 1E9;

            return PHP_INT_SIZE === 4 ? $ns : (int) $ns;
        }

        return [$s, (int) $ns];
    }
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr)
    {
        foreach ($arr as $key => $unused) {
            return $key;
        }
    }
}

if (!function_exists('array_key_last')) {
    function array_key_last(array $array)
    {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
    }

    if (!function_exists('is_countable')) {
        function is_countable($var)
        {
            $countable = (
                is_array($var)
                || is_object($var)
                || is_iterable($var)
                || $var instanceof \Countable
                || $var instanceof \SimpleXMLElement
            );

            return $countable && (class_exists('ResourceBundle')
                ? ($countable instanceof \ResourceBundle) : $countable);
        }
    }
}

// ---------------------------------------------------------------------
// PHP 7.4+
// ---------------------------------------------------------------------
if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $split_length = 1, $encoding = null)
    {
        if (null !== $string
        && !is_scalar($string)
        && !(is_object($string) && method_exists($string, '__toString'))) {
            $type = gettype($string);
            $message = 'mb_str_split() expects parameter 1 to be string, '.$type.' given';
            trigger_error($message, E_USER_WARNING);

            return;
        }

        if (1 > $split_length = (int) $split_length) {
            $message = 'The length of each segment must be greater than zero';
            trigger_error($message, E_USER_WARNING);

            return false;
        }

        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        $encoding = strtoupper($encoding);

        if ('UTF-8' === $encoding) {
            return preg_split(
                "/(.{{$split_length}})/u",
                $string,
                null,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        }

        if ('8BIT' === $encoding || 'BINARY' === $encoding) {
            $encoding = 'CP850';
        }

        $result = [];
        $length = strlen($string, $encoding);

        for ($i = 0; $i < $length; $i += $split_length) {
            $result[] = mb_substr($string, $i, $split_length, $encoding);
        }

        return $result;
    }
}

if (!function_exists('get_mangled_object_vars')) {
    function get_mangled_object_vars($obj)
    {
        if (!is_object($obj)) {
            $type = gettype($obj);
            $message = 'get_mangled_object_vars() expects parameter 1 to be object, '.$type.' given';
            trigger_error($message, E_USER_WARNING);

            return;
        }

        if ($obj instanceof \ArrayIterator || $obj instanceof \ArrayObject) {
            $reflector = new \ReflectionClass(
                $obj instanceof \ArrayIterator ? 'ArrayIterator' : 'ArrayObject'
            );

            $flags = $reflector->getMethod('getFlags')->invoke($obj);
            $reflector = $reflector->getMethod('setFlags');

            $reflector->invoke(
                $obj,
                ($flags & \ArrayObject::STD_PROP_LIST) ? 0 : \ArrayObject::STD_PROP_LIST
            );

            $arr = (array) $obj;
            $reflector->invoke($obj, $flags);
        } else {
            $arr = (array) $obj;
        }

        return array_combine(array_keys($arr), array_values($arr));
    }
}

if (!function_exists('password_algos')) {
    function password_algos()
    {
        $algos = [];
        if (defined('PASSWORD_BCRYPT')) {
            $algos[] = PASSWORD_BCRYPT;
        }

        if (defined('PASSWORD_ARGON2I')) {
            $algos[] = PASSWORD_ARGON2I;
        }

        if (defined('PASSWORD_ARGON2ID')) {
            $algos[] = PASSWORD_ARGON2ID;
        }

        return $algos;
    }
}
