<?php

namespace System\Libraries\Auth;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use System\Core\Config;

class Auth
{
    public static $drivers = [];
    public static $registrar = [];

    /**
     * Ambil instance auth driver.
     *
     * @param string $driver
     *
     * @return \System\Libraries\Auth\Drivers\Driver
     */
    public static function driver($driver = null)
    {
        if (is_null($driver)) {
            $driver = Config::get('auth.driver', 'default');
        }

        if (!isset(static::$drivers[$driver])) {
            static::$drivers[$driver] = static::factory($driver);
        }

        return static::$drivers[$driver];
    }

    /**
     * Buat instance auth driver baru.
     *
     * @param string $driver
     *
     * @return \System\Libraries\Auth\Drivers\Driver
     */
    protected static function factory($driver)
    {
        if (isset(static::$registrar[$driver])) {
            $resolver = static::$registrar[$driver];

            return $resolver();
        }

        switch ($driver) {
            case 'default': return new Drivers\DefaultDriver('users');
            case 'model':   return new Drivers\ModelDriver('\App\Models\User');
            default:        throw new Exception("Auth driver [$driver] is not supported.");
        }
    }

    /**
     * Daftarkan custom (third-party) driver.
     *
     * @param string   $driver
     * @param \Closure $resolver
     *
     * @return void
     */
    public static function extend($driver, Closure $resolver)
    {
        static::$registrar[$driver] = $resolver;
    }

    /**
     * Magic method unntuk pemanggilan method pada default driver (static).
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([static::driver(), $method], $parameters);
    }

    /**
     * Magic method unntuk pemanggilan method pada default driver (object).
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([static::driver(), $method], $parameters);
    }
}
