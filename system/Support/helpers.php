<?php

use System\Core\Config;
use System\Core\Router;
use System\Debugger\Debugger;
use System\Debugger\Dumper;
use System\Support\Arr;
use System\Support\Collection;
use System\Support\Env;
use System\Support\Str;

// ---------------------------------------------------------------------
// System
// ---------------------------------------------------------------------
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('env_get')) {
    function env_get($key, $default = false)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('env_put')) {
    function env_put($key, $value)
    {
        return Env::put($key, $value);
    }
}

if (!function_exists('config')) {
    function config($params)
    {
        return Config::get($params);
    }
}

if (!function_exists('request')) {
    function request($params = null)
    {
        $request = Request::getRequestMethod();
        switch ($request) {
            case     'GET':    $request = Request::get(); break;
            case     'POST':   $request = Request::post(); break;
            case     'PUT':    $request = Request::put(); break;
            case     'PATCH':  $request = Request::patch(); break;
            case     'DELETE': $request = Request::delete(); break;
            default:           $request = Request::all();
        }

        if (is_null($params)) {
            return $request;
        }

        $data = null;
        if (is_array($params)) {
            foreach ($params as $param) {
                $data[$param] = $request[$param];
            }

            return $data;
        }

        return isset($request[$params]) ? $request[$params] : null;
    }
}

if (!function_exists('event')) {
    function event($name, $action = null, array $params = [])
    {
        $event = Event::listener($name);
        if (filled($action)) {
            $event->action($action);
        }

        if (filled($params)) {
            $event->params($params);
        }

        return $event->fire();
    }
}

if (!function_exists('view')) {
    function view($name, array $data = [], $returnOnly = false)
    {
        return View::make($name, $data = [], $returnOnly);
    }
}

// ---------------------------------------------------------------------
// Languages
// ---------------------------------------------------------------------
if (!function_exists('get_lang')) {
    function get_lang()
    {
        $lang = Config::get('app.default_language', 'en');
        if (Session::has(md5('lang'))) {
            return Session::get(md5('lang'));
        }

        Session::put(md5('lang'), $lang);

        return $lang;
    }
}

if (!function_exists('set_lang')) {
    function set_lang($lang = '')
    {
        if (!is_string($lang)) {
            return false;
        }

        if (empty($lang)) {
            $lang = Config::get('app.default_language', 'en');
        }

        Session::put(md5('lang'), $lang);
    }
}

if (!function_exists('lang')) {
    function lang($params)
    {
        $keys = explode('.', $params);
        $file = $keys[0];

        $file = strtolower($file);
        $default = Config::get('app.default_language');
        $path = resources_path('lang/'.$default.'/'.$file.'.php');

        if (!is_file($path)) {
            throw new RuntimeException('Language file not found: '.$path);
        }

        $lang = require_once $path;

        array_shift($keys);

        foreach ($keys as $key) {
            $lang = $lang[$key];
        }

        return $lang;
    }
}

// ---------------------------------------------------------------------
// Path
// ---------------------------------------------------------------------
if (!function_exists('base_path')) {
    function base_path($path = null)
    {
        $path = str_replace(['/', '\\'], [DS, DS], ltrim(ltrim($path, '/'), '\\'));

        return BASE_PATH.$path;
    }
}

if (!function_exists('app_path')) {
    function app_path($path = null)
    {
        $path = str_replace(['/', '\\'], [DS, DS], ltrim(ltrim($path, '/'), '\\'));

        return APP_PATH.$path;
    }
}

if (!function_exists('system_path')) {
    function system_path($path = null)
    {
        $path = str_replace(['/', '\\'], [DS, DS], ltrim(ltrim($path, '/'), '\\'));

        return SYSTEM_PATH.$path;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = null)
    {
        $path = str_replace(['/', '\\'], [DS, DS], ltrim(ltrim($path, '/'), '\\'));

        return STORAGE_PATH.$path;
    }
}

if (!function_exists('uploads_path')) {
    function uploads_path($path = null)
    {
        $path = storage_path('app/uploads/'.ltrim(ltrim($path, '/'), '\\'));

        return $path;
    }
}

if (!function_exists('database_path')) {
    function database_path($path = null)
    {
        $path = str_replace(['/', '\\'], [DS, DS], ltrim(ltrim($path, '/'), '\\'));

        return DATABASE_PATH.$path;
    }
}

if (!function_exists('resources_path')) {
    function resources_path($path = null)
    {
        $path = RESOURCES_PATH.ltrim(ltrim($path, '/'), '\\');

        return $path;
    }
}

// ---------------------------------------------------------------------
// Http / URL
// ---------------------------------------------------------------------
if (!function_exists('base_url')) {
    function base_url($url = null)
    {
        return Request::baseUrl(ltrim(BASE_PATH, '/').'/'.ltrim($url, '/'));
    }
}

if (!function_exists('current_url')) {
    function current_url()
    {
        return (isset($_SERVER['HTTPS'])
            && 'off' !== $_SERVER['HTTPS'] ? 'https' : 'http').'://'.
                $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
}

if (!function_exists('link_to')) {
    function link_to($url)
    {
        return BASE_PATH.'/'.$url;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $delay = 0)
    {
        if ($delay > 0) {
            header('Refresh: '.$delay.';url='.$url);
        } else {
            header('Location: '.$url);
        }
    }
}

if (!function_exists('slug')) {
    function slug($str, $separator = '-')
    {
        $language = Config::get('app.default_language', 'en');

        return Str::slug($title, $separator, $language);
    }
}

if (!function_exists('route')) {
    function route($name, array $params = [])
    {
        return link_to(Router::getUrl($name, $params));
    }
}

// ---------------------------------------------------------------------
// Debugging
// ---------------------------------------------------------------------
if (!function_exists('dd')) {
    function dd()
    {
        $variables = func_get_args();
        if (is_cli()) {
            array_map(function ($var) {
                if (windows_os()) {
                    echo Dumper::toText($var);
                } else {
                    echo Dumper::toTerminal($var);
                }
            }, $variables);
        } else {
            array_map('System\Debugger\Debugger::dump', $variables);
        }

        if (!Debugger::$productionMode || is_cli()) {
            exit();
        }
    }
}

if (!function_exists('bd')) {
    function bd($var, $title = null)
    {
        return Debugger::barDump($var, $title);
    }
}

if (!function_exists('dump')) {
    function dump($var)
    {
        array_map('System\Debugger\Debugger::dump', func_get_args());

        return $var;
    }
}

if (!function_exists('write_log')) {
    function write_log($message, $type = 'info')
    {
        $type = is_string($type) ? strtolower($type) : 'info';
        $types = ['info', 'warning', 'error', 'debug', 'exception', 'critical'];
        $type = in_array($type, $types) ? $type : 'info';

        return Debugger::log($message, $type);
    }
}

if (!function_exists('timer')) {
    function timer($name = null)
    {
        $name = is_string($name) ? $name : null;

        return Debugger::timer($name);
    }
}

// ---------------------------------------------------------------------
// General
// ---------------------------------------------------------------------
if (!function_exists('append_config')) {
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;
                $array[$start] = Arr::pull($array, $key);
            }
        }

        return $array;
    }
}

if (!function_exists('blank')) {
    function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return '' === trim($value);
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return 0 === count($value);
        }

        return empty($value);
    }
}

if (!function_exists('class_basename')) {
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        $class = str_replace('\\', '/', $class);

        return basename($class);
    }
}

if (!function_exists('class_uses_recursive')) {
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('collect')) {
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (!function_exists('data_fill')) {
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('data_get')) {
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ('*' === $segment) {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('data_set')) {
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if ('*' === ($segment = array_shift($segments))) {
            if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

if (!function_exists('e')) {
    function e($value, $doubleEncode = true)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (!function_exists('filled')) {
    function filled($value)
    {
        return !blank($value);
    }
}

if (!function_exists('head')) {
    function head($array)
    {
        return reset($array);
    }
}

if (!function_exists('last')) {
    function last($array)
    {
        return end($array);
    }
}

if (!function_exists('object_get')) {
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || '' == trim($key)) {
            return $object;
        }

        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (!function_exists('preg_replace_array')) {
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (!function_exists('retry')) {
    function retry($times, callable $callback, $sleep = 0, $when = null)
    {
        $attempts = 0;
        $times--;

        beginning:
        $attempts++;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if (!$times || ($when && !$when($e))) {
                throw $e;
            }

            $times--;

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('tap')) {
    function tap($value, callable $callback)
    {
        $callback($value);

        return $value;
    }
}

if (!function_exists('throw_if')) {
    function throw_if($condition, $exception, $message)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception($message) : $exception);
        }

        return $condition;
    }
}

if (!function_exists('throw_unless')) {
    function throw_unless($condition, $exception, $message)
    {
        if (!$condition) {
            throw (is_string($exception) ? new $exception($message) : $exception);
        }

        return $condition;
    }
}

if (!function_exists('trait_uses_recursive')) {
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('transform')) {
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if (!function_exists('value')) {
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('windows_os')) {
    function windows_os()
    {
        return 'win' === strtolower(substr(PHP_OS, 0, 3));
    }
}

if (!function_exists('is_cli')) {
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}

if (!function_exists('with')) {
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

// ---------------------------------------------------------------------
// Array
// ---------------------------------------------------------------------
if (!function_exists('array_add')) {
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if (!function_exists('array_collapse')) {
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}

if (!function_exists('array_divide')) {
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}

if (!function_exists('array_dot')) {
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}

if (!function_exists('array_except')) {
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (!function_exists('array_first')) {
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}

if (!function_exists('array_forget')) {
    function array_forget(&$array, $keys)
    {
        Arr::forget($array, $keys);
    }
}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('array_has')) {
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}

if (!function_exists('array_last')) {
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if (!function_exists('array_only')) {
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (!function_exists('array_pluck')) {
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (!function_exists('array_prepend')) {
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (!function_exists('array_pull')) {
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (!function_exists('array_random')) {
    function array_random($array, $num = null)
    {
        return Arr::random($array, $num);
    }
}

if (!function_exists('array_set')) {
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (!function_exists('array_sort')) {
    function array_sort($array, $callback = null)
    {
        return Arr::sort($array, $callback);
    }
}

if (!function_exists('array_sort_recursive')) {
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}

if (!function_exists('array_where')) {
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}

// ---------------------------------------------------------------------
// String helper
// ---------------------------------------------------------------------
if (!function_exists('camel_case')) {
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (!function_exists('ends_with')) {
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (!function_exists('kebab_case')) {
    function kebab_case($value)
    {
        return Str::kebab($value);
    }
}

if (!function_exists('snake_case')) {
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}

if (!function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (!function_exists('str_after')) {
    function str_after($subject, $search)
    {
        return Str::after($subject, $search);
    }
}

if (!function_exists('str_before')) {
    function str_before($subject, $search)
    {
        return Str::before($subject, $search);
    }
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}

if (!function_exists('str_finish')) {
    function str_finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }
}

if (!function_exists('str_is')) {
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}

if (!function_exists('str_limit')) {
    function str_limit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }
}

if (!function_exists('str_plural')) {
    function str_plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }
}

if (!function_exists('str_random')) {
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (!function_exists('str_replace_array')) {
    function str_replace_array($search, array $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (!function_exists('str_replace_last')) {
    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (!function_exists('str_singular')) {
    function str_singular($value)
    {
        return Str::singular($value);
    }
}

if (!function_exists('str_slug')) {
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
}

if (!function_exists('str_start')) {
    function str_start($value, $prefix)
    {
        return Str::start($value, $prefix);
    }
}

if (!function_exists('studly_case')) {
    function studly_case($value)
    {
        return Str::studly($value);
    }
}

if (!function_exists('title_case')) {
    function title_case($value)
    {
        return Str::title($value);
    }
}
