<?php

namespace System\Libraries\Session;

defined('DS') or exit('No direct script access allowed.');

use System\Core\Config;
use System\Debugger\Debugger;

class Session
{
    private $config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('sessions');

        if (true === $this->config['cookie_httponly']) {
            ini_set('session.cookie_httponly', 1);
        }

        if (true === $this->config['use_only_cookies']) {
            ini_set('session.use_only_cookies', 1);
        }

        ini_set('session.gc_maxlifetime', $this->config['lifetime']);
        session_set_cookie_params($this->config['lifetime']);

        $this->init();
    }

    /**
     * Mulai session.
     */
    private function init()
    {
        if (!$this->started()) {
            if (!Debugger::isEnabled()) {
                if (!Debugger::DETECT) {
                    Debugger::dispatch();
                }
            }

            @session_start();
            Debugger::dispatch();
            $this->put('session_id', $this->generateSessionId());
        } else {
            if (!hash_equals($this->get('session_id'), $this->generateSessionId())) {
                $this->destroy();
            }
        }

        $this->put('csrf_token', $this->generateCsrfToken());
    }

    /**
     * Cek apakah session sudah aktif atau belum.
     *
     * @return bool
     */
    public function started()
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * Simpan data ke session.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function put($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $key => $value) {
                $_SESSION[$key] = $value;
            }
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Ambil data dari session.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }

    /**
     * Cek apakah session key ada.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Lupakan / unset data session.
     *
     * @param string $key
     */
    public function forget($key = null)
    {
        if (is_null($key)) {
            session_unset();
        } else {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy / hapus semua data.
     */
    public function destroy()
    {
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }

        session_unset();
        session_destroy();
    }

    /**
     * Set session flash.
     *
     * @param string $message
     * @param string $url
     */
    public function setFlash($message, $url = null)
    {
        $this->set('flash', $message);

        if (!is_null($url)) {
            header("Location: $url");
            exit();
        }
    }

    /**
     * Ambil session flasl.
     *
     * @return mixed
     */
    public function getFlash()
    {
        $flash = $this->get('flash');
        $this->forget('flash');

        return $flash;
    }

    /**
     * Cek apakah ada session flash yang tersimpan atau tidak.
     *
     * @return bool
     */
    public function hasFlash()
    {
        return $this->has('flash');
    }

    /**
     * Ambil semua sssion (termasuk session_id, dan csrf_token).
     *
     * @return array
     */
    public function all()
    {
        $sessions = $_SESSION;
        unset($sessions['__DEBUGGER']);

        return $sessions;
    }

    /**
     * Ambil value CSRF token.
     *
     * @return string
     */
    public function token()
    {
        return $this->get('csrf_token');
    }

    /**
     * Ambil value session id.
     *
     * @return string
     */
    public function id()
    {
        return $this->get('session_id');
    }

    /**
     * Buat session hash.
     *
     * @return string
     */
    private function generateSessionId()
    {
        $string = Config::get('app.application_key');

        if (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $string .= $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'];
        }

        $string .= random_bytes(16);

        return md5(base64_encode($string));
    }

    /**
     * Buat token anti CSRF.
     *
     * @return string
     */
    private function generateCsrfToken()
    {
        return base64_encode(random_bytes(16).microtime(true));
    }
}
