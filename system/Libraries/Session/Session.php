<?php

namespace System\Libraries\Session;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use System\Core\Config;
use System\Database\Database;
use System\Facades\Cookie;

class Session
{
    protected $driver;
    protected $registrar = [];

    /**
     * Buat payload Session baru.
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * Buat payload session dan load library session.
     *
     * @return void
     */
    public function load()
    {
        $this->start(Config::get('session.driver'));
        $this->driver->load(Cookie::get(Config::get('session.cookie')));
    }

    /**
     * Buat payload session untuk request saat ini.
     *
     * @param  string $driver
     *
     * @return void
     */
    public function start($driver)
    {
        $this->driver = new Payload($this->factory($driver));
    }

    /**
     * Buat payload baru session driver.
     *
     * @param  string $driver
     *
     * @return \System\Libraries\Session\Drivers\Driver
     */
    public function factory($driver)
    {
        if (isset($this->registrar[$driver])) {
            $resolver = $this->registrar[$driver];

            return $resolver();
        }

        switch ($driver) {
            case 'cookie': return new Drivers\CookieDriver();
            case 'database': return new Drivers\DbDriver(Database::connection());
            case 'file': return new Drivers\FileDriver(storage_path('sytem/sessions/'));
            case 'memory': return new Drivers\MemoryDriver();
            default: throw new Exception("Session driver [$driver] is not supported.");
        }
    }

    /**
     * Ambil daftar registrar diver.
     *
     * @return array
     */
    public function registrar()
    {
        return $this->registrar;
    }

    /**
     * Ambil object driver saat ini.
     *
     * @return \System\Libraries\Session\Payload
     */
    public function driver()
    {
        if ($this->started()) {
            return $this->driver;
        }

        throw new Exception("A driver must be set before using the session.");
    }

    /**
     * Cek apakah session handler sudah di start atau belum
     *
     * @return bool
     */
    public function started()
    {
        return !is_null($this->driver);
    }

    /**
     * Daftarkan session driver baru
     *
     * @param  strung   $driver
     * @param  \Closure $resolver
     *
     * @return void
     */
    public function extend($driver, Closure $resolver)
    {
        $this->registrar[$driver] = $resolver;
    }

    /**
     * Magic Method call.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}
