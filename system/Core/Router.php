<?php

namespace System\Core;

defined('DS') or exit('No direct script access allowed.');

use App\Http\Kernel as AppHttpKernel;
use Closure;
use RuntimeException;
use System\Facades\Response;
use System\Facades\View;
use System\Support\Arr;
use System\Support\Str;

class Router
{
    private static $routes = [];
    private static $middlewares = [];
    private static $prefix = [];
    private static $baseroute = '/';
    private static $namespace = '';
    private static $domain = '';
    private static $ip = '';
    private static $groups = [];
    private static $names = [];
    private static $grouped = 0;
    private static $ssl = false;
    private static $patterns = [
        '{all}'   => '([^/]+)',
        '{num}'   => '([0-9]+)',
        '{alpha}' => '([a-zA-Z]+)',
        '{alnum}' => '([a-zA-Z0-9_-]+)',
    ];

    protected static $baseNamespace = 'App\\Http\\Controllers\\';

    /**
     * Route group.
     *
     * @param \Closure $callback
     */
    public static function group(Closure $callback)
    {
        static::$grouped++;

        static::$groups[] = [
            'baseroute'   => static::$baseroute,
            'middlewares' => static::$middlewares,
            'namespaces'  => static::$namespace,
            'domain'      => static::$domain,
            'ip'          => static::$ip,
            'ssl'         => static::$ssl,
            'prefix'      => static::$prefix,
            'names'       => static::$names,
        ];

        call_user_func($callback);

        if (static::$grouped > 0) {
            $group = static::$groups[static::$grouped - 1];

            static::$baseroute = $group['baseroute'];
            static::$middlewares = $group['middlewares'];
            static::$namespace = $group['namespaces'];
            static::$domain = $group['domain'];
            static::$ip = $group['ip'];
            static::$ssl = $group['ssl'];
            static::$prefix = $group['prefix'];
            static::$names = $group['names'];
        }

        static::$grouped--;

        if (static::$grouped <= 0) {
            // reset
            static::$baseroute = '/';
            static::$middlewares = [];
            static::$namespace = '';
            static::$domain = '';
            static::$ip = '';
            static::$ssl = false;
            static::$prefix = [];
            static::$names = [];
        }
    }

    /**
     * Ambil list data grup yang terdaftar
     *
     * @return array
     */
    public static function getGroups()
    {
        return static::$groups;
    }

    /**
     * Set namespace grup route.
     *
     * @param string $namespace
     */
    public static function setNamespace($namespace)
    {
        static::$namespace = $namespace;

        return new static();
    }

    /**
     * Get namespace grup route.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return static::$namespace;
    }

    /**
     * Paggril middleware untuk route saat ini.
     *
     * @param string|array $names
     *
     * @return object
     */
    public static function middleware($names)
    {
        $names = (array) $names;
        $locals = AppHttpKernel::$localMiddlewareGroups;

        foreach ($names as $name) {
            if (!isset($locals[$name])) {
                throw new RuntimeException("No local middleware found with this name: {$name}");
            }

            $classes = $locals[$name];
            $classes = Arr::wrap($classes);

            foreach ($classes as $class) {
                if (!class_exists($class)) {
                    throw new RuntimeException("Local middleware class not found for '{$name}': {$class}");
                }

                static::addMiddleware($name, $class);
            }
        }

        return new static();
    }

    /**
     * Tambahkan middleware ke middlewre list.
     *
     * @param string $name
     * @param string $class
     */
    private static function addMiddleware($name, $class)
    {
        static::$middlewares[$name] = $class.'@handle';
    }

    /**
     * Ambil satu atau seluruh middleware
     *
     * @param  string $name
     *
     * @return string
     */
    public static function getMiddleware($name = null)
    {
        if (null === $name) {
            return static::$middlewares;
        }

        if (static::hasMiddleware($name)) {
            return static::$middlewares[$name];
        }

        return null;
    }

    /**
     * Cek apakah middleware sudah terdaftar atau belum
     *
     * @param  string $name
     *
     * @return bool
     */
    public static function hasMiddleware($name)
    {
        return array_key_exists($name, static::$middlewares);
    }

    /**
     * Route berdasarkan prefix URL.
     *
     * @param string $prefix
     *
     * @return object
     */
    public static function prefix($prefix)
    {
        self::$prefix[] = $prefix;
        static::$baseroute = '/'.implode('/', static::$prefix);

        return new static();
    }

    /**
     * Route berdasarkan domain.
     *
     * @param string $domain
     *
     * @return object
     */
    public static function domain($domain)
    {
        static::$domain = $domain;

        return new static();
    }

    /**
     * Route berdasarkan ip.
     *
     * @param string $ip
     *
     * @return object
     */
    public static function ip($ip)
    {
        static::$ip = $ip;

        return new static();
    }

    /**
     * Set SSL routing.
     *
     * @param bool $state
     *
     * @return object
     */
    public static function ssl($state = true)
    {
        static::$ssl = (true === $state);

        return new static();
    }

    /**
     * Daftarkan route.
     *
     * @param string          $method
     * @param string          $pattern
     * @param string|callable $callback
     */
    public static function route($method, $pattern, $callback)
    {
        if ('/' == $pattern) {
            $pattern = static::$baseroute.trim($pattern, '/');
        } else {
            if ('/' == static::$baseroute) {
                $pattern = static::$baseroute.trim($pattern, '/');
            } else {
                $pattern = static::$baseroute.$pattern;
            }
        }

        $uri = $pattern;
        $pattern = preg_replace('/[\[{\(].*[\]}\)]/U', '([^/]+)', $pattern);
        $pattern = '/^'.str_replace('/', '\/', $pattern).'$/';

        $handler = false;
        if (is_callable($callback) || $callback instanceof Closure) {
            $handler = $callback;
        } elseif (false !== stripos($callback, '@')) {
            if (static::$namespace) {
                $namespaces = Str::studly(static::$namespace);
                $handler = static::$baseNamespace.$namespaces.'\\'.$callback;
            } else {
                $handler = static::$baseNamespace.$callback;
            }
        } else {
            throw new RuntimeException('Invalid route handler format: '.$callback);
        }

        $routes = [
            'uri'      => $uri,
            'method'   => $method,
            'pattern'  => $pattern,
            'callback' => $handler,
        ];

        $global = AppHttpKernel::$globalMiddlewareGroups;
        if (!empty($global)) {
            foreach ($global as $name => $class) {
                static::addMiddleware($name, $class);
            }
        }

        if (static::$namespace) {
            $routes['namespaces'] = Str::studly(static::$namespace);
        }

        if (!empty(static::$middlewares)) {
            $routes['middlewares'] = static::$middlewares;
        }

        if (static::$domain) {
            $routes['domain'] = static::$domain;
        }

        if (static::$ip) {
            $routes['ip'] = static::$ip;
        }

        if (static::$ssl) {
            $routes['ssl'] = static::$ssl;
        }

        static::$routes[] = $routes;
    }

    /**
     * Eksekusi route yang telah didaftarkan.
     */
    public static function run()
    {
        $matched = 0;
        foreach (static::$routes as $key => $route) {
            if (preg_match($route['pattern'], static::getCurrentUri(), $params)) {
                $domainCheck = static::checkDomain($route);
                $ipCheck = static::checkIp($route);
                $sslCheck = static::checkSSL($route);
                $methodCheck = static::checkMethod($route);

                if ($domainCheck && $methodCheck && $ipCheck && $sslCheck) {
                    $matched++;
                    array_shift($params);
                    if (isset($route['middlewares'])) {
                        foreach ($route['middlewares'] as $name => $middleware) {
                            list($class, $method) = explode('@', $middleware);
                            if (class_exists($class)) {
                                $object = new $class();
                                if (!method_exists($object, 'handle')) {
                                    throw new RuntimeException(
                                       'Middleware handler not found: '.$class.'::handle()'
                                   );
                                }

                                call_user_func_array([$object, $method], []);
                            } else {
                                throw new RuntimeException('Middleware class not found: '.$class);
                            }
                        }
                    }

                    if (is_callable($route['callback'])) {
                        call_user_func_array($route['callback'], array_values($params));
                    } elseif (false !== stripos($route['callback'], '@')) {
                        list($controller, $method) = explode('@', $route['callback']);
                        if (class_exists($controller)) {
                            call_user_func_array([new $controller(), $method], array_values($params));
                        } else {
                            static::showPageNotFound();
                        }
                    }

                    break;
                }
            }
        }

        if (0 === $matched) {
            static::showPageNotFound();
        }
    }

    /**
     * Cek keabsahan domain.
     *
     * @param array $params
     *
     * @return bool
     */
    private static function checkDomain(array $params)
    {
        if (isset($params['domain'])) {
            $domain = trim(str_replace('www.', '', $_SERVER['SERVER_NAME']), '/');
            if ($params['domain'] !== $domain) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Cek keabsahan method.
     *
     * @param array $params
     *
     * @return bool
     */
    private static function checkMethod(array $params)
    {
        if ($params['method'] !== static::getRequestMethod()) {
            return false;
        }

        return true;
    }

    /**
     * Cek keabsahan ip.
     *
     * @param array $params
     *
     * @return bool
     */
    private static function checkIp(array $params)
    {
        if (isset($params['ip'])) {
            if (is_array($params['ip'])) {
                if (!in_array($_SERVER['REMOTE_ADDR'], $params['ip'])) {
                    return false;
                }

                return true;
            }

            if ($_SERVER['REMOTE_ADDR'] != $params['ip']) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Cek keabsahan ssl.
     *
     * @param array $params
     *
     * @return bool
     */
    private static function checkSSL(array $params)
    {
        if (array_key_exists('ssl', $params) && true === $params['ssl']) {
            if ('https' !== $_SERVER['REQUEST_SCHEME']) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Tangani kondisi saat route handler tidak ditemukan.
     *
     * @return void
     */
    private static function showPageNotFound()
    {
        $content = '404 Page Not Found!';
        if (View::exists('errors.notfound')) {
            $content = View::make('errors.notfound', [], true);
        } else {
            $content = system_path('Debugger/assets/debugger/errors/notfound.php');
            $content = file_get_contents($content);
        }

        Response::status(404)->body($content)->send();
    }

    /**
     * Ambil URI saat ini dari query string.
     *
     * @return string
     */
    public static function getCurrentUri()
    {
        if (is_cli()) {
            $uri = '/';
        } else {
            $uri = substr($_SERVER['REQUEST_URI'], strlen(static::getBasePath()));
        }

        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        if (is_cli()) {
            $uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
        }

        return '/'.trim($uri, '/');
    }

    /**
     * Ambil base path.
     *
     * @return string
     */
    public static function getBasePath()
    {
        $scriptName = array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1);

        return implode('/', $scriptName).'/';
    }

    /**
     * Ambil request header.
     *
     * @return array
     */
    public static function getRequestHeaders()
    {
        return getallheaders();
    }

    /**
     * Ambil request method.
     *
     * @return string
     */
    public static function getRequestMethod()
    {
        if (is_cli()) {
            $method = 'GET';
        } else {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        if ('HEAD' == $method) {
            ob_start();
            $method = 'GET';
        } elseif ('POST' == $method) {
            $headers = static::getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override'])
            && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
     * Daftarkan route dengan method GET.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function get($pattern, $callback)
    {
        static::route('GET', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan method POST.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function post($pattern, $callback)
    {
        static::route('POST', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan method PATCH.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function patch($pattern, $callback)
    {
        static::route('PATCH', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan method DELETE.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function delete($pattern, $callback)
    {
        static::route('DELETE', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan method PUT.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function put($pattern, $callback)
    {
        static::route('PUT', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan method OPTIONS.
     *
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return object
     */
    public static function options($pattern, $callback)
    {
        static::route('OPTIONS', $pattern, $callback);

        return new static();
    }

    /**
     * Daftarkan route dengan lebih dari satu method.
     *
     * @param array           $methods
     * @param string          $pattern
     * @param string|callable $callback
     *
     * @return void
     */
    public static function match(array $methods, $pattern, $callback)
    {
        foreach ($methods as $method) {
            static::route(strtoupper($method), $pattern, $callback);
        }
    }

    /**
     * Beri filter untuk parameter route.
     *
     * @param array $expressions
     *
     * @return object
     */
    public function where(array $expressions)
    {
        $routeKey = array_search(end(static::$routes), static::$routes);

        $pattern = static::resolveRoutePattern(static::$routes[$routeKey]['uri'], $expressions);
        $pattern = '/'.implode('/', $pattern);
        $pattern = '/^'.str_replace('/', '\/?', $pattern).'$/';

        static::$routes[$routeKey]['pattern'] = $pattern;

        return new static();
    }

    /**
     * Beri nama route.
     *
     * @param string $name
     * @param array  $params
     *
     * @return object
     */
    public static function name($name, array $params = [])
    {
        $routeKey = array_search(end(static::$routes), static::$routes);
        static::$routes[$routeKey]['name'] = $name;

        return new static();
    }

    /**
     * Ambil URL dari route name.
     *
     * @param string $name
     * @param array  $params
     *
     * @return string|null
     */
    public static function getUrl($name, array $params = [])
    {
        $pattern = null;
        foreach (static::$routes as $route) {
            if (array_key_exists('name', $route) && $route['name'] == $name) {
                $pattern = static::resolveRoutePattern($route['uri'], $params);
                $pattern = implode('/', $pattern);
                break;
            }
        }

        return $pattern;
    }

    /**
     * Ambil seluruh routing yang terdaftar.
     *
     * @return array
     */
    public static function getRoutes()
    {
        return static::$routes;
    }

    /**
     * Ubah pola {foo} menjadi pola yang sah.
     *
     * @param string $uri
     * @param array  $expressions
     *
     * @return array
     */
    private static function resolveRoutePattern($uri, array $expressions = [])
    {
        $pattern = explode('/', ltrim($uri, '/'));
        foreach ($pattern as $key => $val) {
            if (preg_match('/[\[{\(].*[\]}\)]/U', $val, $matches)) {
                foreach ($matches as $match) {
                    $matchKey = substr($match, 1, -1);
                    if (array_key_exists($matchKey, $expressions)) {
                        $pattern[$key] = $expressions[$matchKey];
                    }
                }
            }
        }

        return $pattern;
    }

    /**
     * Magic method untuk memanggil method kelas secara statis.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return object
     */
    public static function __callStatic($method, $parameters)
    {
        if ('namespaces' === $method) {
            static::setNamespace($parameters[0]);

            return new static();
        }
    }

    /**
     * Magic method untuk memanggil method kelas.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return object
     */
    public function __call($method, $parameters)
    {
        if ('namespaces' === $method) {
            static::setNamespace($parameters[0]);

            return new static();
        }
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
